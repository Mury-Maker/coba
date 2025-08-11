<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\DatabaseData;
use App\Models\DatabaseImage;
use App\Models\DatabaseDocument; // Tambah model dokumen
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class DatabaseDataController extends Controller
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
            'keterangan' => 'nullable|string',
            'relasi' => 'nullable|string',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Tambah validasi
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $databaseData = null;

            $databaseData = DB::transaction(function () use ($request, $useCase) {
                $newDatabaseData = $useCase->databaseData()->create([
                    'keterangan' => $request->keterangan,
                    'relasi' => $request->relasi,
                ]);

                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('database_images', 'public');
                            $newDatabaseData->images()->create([
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
                            $path = $documentFile->store('database_documents', 'public');
                            $newDatabaseData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $newDatabaseData;
            });

            return response()->json([
                'success' => 'Data Database berhasil ditambahkan!',
                'database_data' => $databaseData->load(['images', 'documents']) // Muat relasi dokumen
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, DatabaseData $databaseData)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'keterangan' => 'nullable|string',
            'relasi' => 'nullable|string',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Tambah validasi
            'existing_images_kept.*' => 'nullable|integer',
            'existing_documents_kept.*' => 'nullable|integer', // Tambah validasi
        ]);

        try {
            $updatedDatabaseData = DB::transaction(function () use ($request, $databaseData) {
                $databaseData->update($request->only([
                    'keterangan',
                    'relasi',
                ]));

                $keptImageIds = collect($request->input('existing_images_kept', []))->map(fn($id) => (int)$id)->toArray();
                $keptDocumentIds = collect($request->input('existing_documents_kept', []))->map(fn($id) => (int)$id)->toArray();

                // Hapus gambar yang tidak dipertahankan
                $databaseData->images()->whereNotIn('id', $keptImageIds)->get()->each(function ($image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                });

                // Hapus dokumen yang tidak dipertahankan
                $databaseData->documents()->whereNotIn('id', $keptDocumentIds)->get()->each(function ($document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                });

                // Tambahkan gambar baru
                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('database_images', 'public');
                            $databaseData->images()->create([
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
                            $path = $documentFile->store('database_documents', 'public');
                            $databaseData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $databaseData;
            });

            return response()->json([
                'success' => 'Data Database berhasil diperbarui!',
                'database_data' => $updatedDatabaseData->load(['images', 'documents'])
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(DatabaseData $databaseData)
    {
        $this->ensureAdminAccess();

        try {
            DB::transaction(function () use ($databaseData) {
                foreach ($databaseData->images as $image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                }
                // Hapus dokumen terkait
                foreach ($databaseData->documents as $document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                }
                $databaseData->delete();
            });
            return response()->json(['success' => 'Data Database berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function cetakPdf($usecase_id)
    {
        $usecase = UseCase::with(['reportData', 'uatData', 'databaseData'])->findOrFail($usecase_id);
        $pdf = Pdf::loadView('pdf.database', compact('usecase'));
        return $pdf->stream('database.pdf');
    }
}
