<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\UatImage;
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
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $uatData = null;

            $uatData = DB::transaction(function () use ($request, $useCase) {
                $uatData = $useCase->uatData()->create([
                    'nama_proses_usecase' => $request->nama_proses_usecase,
                    'keterangan_uat' => $request->keterangan_uat,
                    'status_uat' => $request->status_uat,
                ]);

                if ($request->hasFile('uat_images')) {
                    foreach ($request->file('uat_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('uat_images', 'public');
                            $uatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }
                return $uatData;
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
            'uat_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'uat_images_current.*' => 'nullable|string',
        ]);

        try {
            // Tangkap objek yang dikembalikan dari transaksi
            $updatedUatData = DB::transaction(function () use ($request, $uatData) {
                // Perbarui data dasar
                $uatData->update($request->only([
                    'nama_proses_usecase',
                    'keterangan_uat',
                    'status_uat',
                ]));

                $keptImagePaths = $request->input('uat_images_current', []);

                // Hapus gambar yang tidak dipertahankan
                foreach ($uatData->images as $image) {
                    $storagePath = Str::after($image->path, '/storage/');
                    if (!in_array($image->path, $keptImagePaths) && !in_array($storagePath, $keptImagePaths)) {
                        Storage::disk('public')->delete($storagePath);
                        $image->delete();
                    }
                }

                // Tambahkan gambar baru
                if ($request->hasFile('uat_images')) {
                    foreach ($request->file('uat_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('uat_images', 'public');
                            $uatData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }

                // Kembalikan objek yang sudah diperbarui
                return $uatData;
            });

            return response()->json([
                'success' => 'Data UAT berhasil diperbarui!',
                // Gunakan variabel yang baru
                'uat_data' => $updatedUatData->load('images')
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
