<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\UatImage; // Pastikan model UatImage di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
            'uat_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Mendukung multiple files
            // 'existing_uat_images.*' tidak relevan untuk store
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $uatData = null;

            DB::transaction(function () use ($request, $useCase, &$uatData) {
                $uatData = $useCase->uatData()->create([
                    'nama_proses_usecase' => $request->nama_proses_usecase,
                    'keterangan_uat' => $request->keterangan_uat,
                    'status_uat' => $request->status_uat,
                ]);

                if ($request->hasFile('uat_images')) {
                    foreach ($request->file('uat_images') as $imageFile) {
                        if ($imageFile->isValid()) { // Pastikan file valid
                            $path = $imageFile->store('uat_images', 'public');
                            $uatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => 'Data UAT berhasil ditambahkan!',
                'uat_data' => $uatData->load('images')
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
            'uat_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Untuk file baru
            // Nama input hidden dari imagePreviewer.js adalah 'uat_images_current[]'
            'uat_images_current.*' => 'nullable|string', // Validasi untuk path gambar yang tetap ada
        ]);

        try {
            DB::transaction(function () use ($request, $uatData) {
                $uatData->update($request->only([
                    'nama_proses_usecase',
                    'keterangan_uat',
                    'status_uat',
                ]));

                // Dapatkan path gambar yang harus dipertahankan dari request
                // Jika tidak ada 'uat_images_current', asumsikan semua gambar lama dihapus
                $keptImagePaths = $request->input('uat_images_current', []);

                // Hapus gambar lama yang tidak ada dalam daftar 'keptImagePaths'
                foreach ($uatData->images as $image) {
                    $storagePath = Str::after($image->path, '/storage/');
                    if (!in_array($image->path, $keptImagePaths) && !in_array($storagePath, $keptImagePaths)) {
                        Storage::disk('public')->delete($storagePath); // Hapus file fisik
                        $image->delete(); // Hapus entri dari database
                    }
                }

                // Tambahkan gambar baru
                if ($request->hasFile('uat_images')) {
                    foreach ($request->file('uat_images') as $imageFile) {
                        if ($imageFile->isValid()) { // Pastikan file valid
                            $path = $imageFile->store('uat_images', 'public');
                            $uatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => 'Data UAT berhasil diperbarui!',
                'uat_data' => $uatData->load('images')
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
                $uatData->delete();
            });
            return response()->json(['success' => 'Data UAT berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data UAT: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data UAT.', 'error' => $e->getMessage()], 500);
        }
    }
}
