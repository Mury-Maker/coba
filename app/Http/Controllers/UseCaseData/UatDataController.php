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
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class UatDataController extends Controller
{
    // Helper untuk memastikan pengguna adalah admin
    private function ensureAdminAccess()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin Admin.');
        }
    }

    public function store(Request $request)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'use_case_id' => 'required|exists:use_cases,id',
            'nama_proses_usecase' => 'required|string|max:255',
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:Passed,Failed,Pending',
            'uat_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Untuk multiple files
            'existing_uat_images.*' => 'nullable|string',
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
                        $path = $imageFile->store('uat_images', 'public');
                        $uatData->images()->create([
                            'path' => Storage::url($path),
                            'filename' => $imageFile->getClientOriginalName(),
                        ]);
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
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'nama_proses_usecase' => 'required|string|max:255',
            'keterangan_uat' => 'nullable|string',
            'status_uat' => 'nullable|string|in:Passed,Failed,Pending',
            'uat_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'existing_uat_images.*' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $uatData) {
                $uatData->update($request->only([
                    'nama_proses_usecase',
                    'keterangan_uat',
                    'status_uat',
                ]));

                $existingImagePaths = $request->input('existing_uat_images', []);
                foreach ($uatData->images as $image) {
                    if (!in_array($image->path, $existingImagePaths)) {
                        Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                        $image->delete();
                    }
                }

                if ($request->hasFile('uat_images')) {
                    foreach ($request->file('uat_images') as $imageFile) {
                        $path = $imageFile->store('uat_images', 'public');
                        $uatData->images()->create([
                            'path' => Storage::url($path),
                            'filename' => $imageFile->getClientOriginalName(),
                        ]);
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
        $this->ensureAdminAccess(); // Verifikasi akses Admin

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
