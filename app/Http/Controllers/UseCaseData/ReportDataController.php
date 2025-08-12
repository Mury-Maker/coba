<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\ReportData;
use App\Models\ReportImage;
use App\Models\ReportDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportDataController extends Controller
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
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $reportData = DB::transaction(function () use ($request, $useCase) {
                $newReportData = $useCase->reportData()->create([
                    'aktor' => $request->aktor,
                    'nama_report' => $request->nama_report,
                    'keterangan' => $request->keterangan,
                ]);

                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('report_images', 'public');
                            $newReportData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                if ($request->hasFile('new_documents')) {
                    foreach ($request->file('new_documents') as $documentFile) {
                        if ($documentFile->isValid()) {
                            $path = $documentFile->store('report_documents', 'public');
                            $newReportData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $newReportData;
            });

            // PERUBAHAN: Memuat relasi sebelum respons
            $reportData->load(['images', 'documents']);

            return response()->json([
                'success' => 'Data Report berhasil disimpan!',
                'report_data' => $reportData
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, ReportData $reportData)
    {
        $this->ensureAdminAccess();

        $request->validate([
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'existing_images_kept.*' => 'nullable|integer',
            'existing_documents_kept.*' => 'nullable|integer',
        ]);

        try {
            $updatedReportData = DB::transaction(function () use ($request, $reportData) {
                $reportData->update([
                    'aktor' => $request->aktor,
                    'nama_report' => $request->nama_report,
                    'keterangan' => $request->keterangan,
                ]);

                $keptImageIds = collect($request->input('existing_images_kept', []))->map(fn($id) => (int)$id)->toArray();
                $keptDocumentIds = collect($request->input('existing_documents_kept', []))->map(fn($id) => (int)$id)->toArray();

                $reportData->images()->whereNotIn('id', $keptImageIds)->get()->each(function ($image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                });

                $reportData->documents()->whereNotIn('id', $keptDocumentIds)->get()->each(function ($document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                });

                if ($request->hasFile('new_images')) {
                    foreach ($request->file('new_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('report_images', 'public');
                            $reportData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                if ($request->hasFile('new_documents')) {
                    foreach ($request->file('new_documents') as $documentFile) {
                        if ($documentFile->isValid()) {
                            $path = $documentFile->store('report_documents', 'public');
                            $reportData->documents()->create([
                                'path' => Storage::url($path),
                                'filename' => $documentFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                return $reportData;
            });

            $updatedReportData->load(['images', 'documents']);

            return response()->json([
                'success' => 'Data Report berhasil diperbarui!',
                'report_data' => $updatedReportData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(ReportData $reportData)
    {
        $this->ensureAdminAccess();

        try {
            DB::transaction(function () use ($reportData) {
                foreach ($reportData->images as $image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                }
                foreach ($reportData->documents as $document) {
                    Storage::disk('public')->delete(Str::after($document->path, '/storage/'));
                    $document->delete();
                }
                $reportData->delete();
            });

            return response()->json(['success' => 'Data Report berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Report.', 'error' => $e->getMessage()], 500);
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
        foreach ($usecase->reportData as $report) {
            foreach ($report->images as $img) {
                $img->full_path = public_path('storage/' . $img->path);
            }
        }
    
        $pdf = Pdf::loadView('pdf.report', compact('usecase'))
                  ->setPaper('A4', 'portrait');
    
        return $pdf->stream('Report.pdf');
    }
}
