<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\UatImage;
use App\Models\UatDocument; // Tambah model dokumen
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class UatDataController extends Controller
{
    private function ensureAdminAccess()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin Admin.');
        }
    }

    public function store(Request $request)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'use_case_id' => 'required|exists:use_cases,id',
            'nama_proses_usecase' => 'required|string|max:255',
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:Passed,Failed,Pending',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Tambah validasi
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $uatData = null;

            $uatData = DB::transaction(function () use ($request, $useCase) {
                $newUatData = $useCase->uatData()->create([
                    'nama_proses_usecase' => $request->nama_proses_usecase,
                    'keterangan_uat' => $request->keterangan_uat,
                    'status_uat' => $request->status_uat,
                ]);

                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('uat_images', 'public');
                            $newUatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                // Tambah logika penyimpanan dokumen
                if ($request->hasFile('new_documents')) {
                    foreach ($request->file('new_documents') as $documentFile) {
                        if ($documentFile->isValid()) {
                            $path = $documentFile->store('uat_documents', 'public');
                            $newUatData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $newUatData;
            });

            return response()->json([
                'success' => 'Data UAT berhasil ditambahkan!',
                'uat_data' => $uatData->load(['images', 'documents']) // Muat relasi dokumen
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, UatData $uatData)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'nama_proses_usecase' => 'required|string|max:255',
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:Passed,Failed,Pending',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Tambah validasi
            'existing_images_kept.*' => 'nullable|integer',
            'existing_documents_kept.*' => 'nullable|integer', // Tambah validasi
        ]);

        try {
            $updatedUatData = DB::transaction(function () use ($request, $uatData) {
                $uatData->update($request->only([
                    'nama_proses_usecase',
                    'keterangan_uat',
                    'status_uat',
                ]));

                $keptImageIds = collect($request->input('existing_images_kept', []))->map(fn($id) => (int)$id)->toArray();
                $keptDocumentIds = collect($request->input('existing_documents_kept', []))->map(fn($id) => (int)$id)->toArray();

                // Hapus gambar yang tidak dipertahankan
                $uatData->images()->whereNotIn('id', $keptImageIds)->get()->each(function ($image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                });

                // Hapus dokumen yang tidak dipertahankan
                $uatData->documents()->whereNotIn('id', $keptDocumentIds)->get()->each(function ($document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                });

                // Tambahkan gambar baru
                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('uat_images', 'public');
                            $uatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                // Tambah logika penyimpanan dokumen
                if ($request->hasFile('new_documents')) {
                    foreach ($request->file('new_documents') as $documentFile) {
                        if ($documentFile->isValid()) {
                            $path = $documentFile->store('uat_documents', 'public');
                            $uatData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $uatData;
            });

            return response()->json([
                'success' => 'Data UAT berhasil diperbarui!',
                'uat_data' => $updatedUatData->load(['images', 'documents'])
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(UatData $uatData)
    {
        $this->ensureAdminAccess();

        try {
            DB::transaction(function () use ($uatData) {
                foreach ($uatData->images as $image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                }
                // Hapus dokumen terkait
                foreach ($uatData->documents as $document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                }
                $uatData->delete();
            });
            return response()->json(['success' => 'Data UAT berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data UAT.', 'error' => $e->getMessage()], 500);
        }
    }

    public function cetakPdf($usecase_id)
    {
        $this->ensureAdminAccess(); // biar aman
    
        $usecase = UseCase::with([
            'uatData.images',
            'reportData.images',
            'databaseData.images'
        ])->findOrFail($usecase_id);
    
        // Pastikan setiap gambar punya path absolut yang bisa dibaca DomPDF
        foreach ($usecase->uatData as $uat) {
            foreach ($uat->images as $img) {
                $img->full_path = public_path('storage/' . $img->path);
            }
        }
    
        foreach ($usecase->reportData as $report) {
            foreach ($report->images as $img) {
                $img->full_path = public_path('storage/' . $img->path);
            }
        }
    
        foreach ($usecase->databaseData as $db) {
            foreach ($db->images as $img) {
                $img->full_path = public_path('storage/' . $img->path);
            }
        }
    
        $pdf = Pdf::loadView('pdf.uat', compact('usecase'))
                  ->setPaper('A4', 'portrait');
    
        return $pdf->stream('UAT.pdf');
    }
}
