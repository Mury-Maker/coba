<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\DatabaseData;
use App\Models\DatabaseImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class DatabaseDataController extends Controller
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
            'keterangan' => 'nullable|string',
            'database_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'relasi' => 'nullable|string',
            'existing_database_images.*' => 'nullable|string',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $databaseData = null;

            DB::transaction(function () use ($request, $useCase, &$databaseData) {
                $databaseData = $useCase->databaseData()->create([
                    'keterangan' => $request->keterangan,
                    'relasi' => $request->relasi,
                ]);

                if ($request->hasFile('database_images')) {
                    foreach ($request->file('database_images') as $imageFile) {
                        $path = $imageFile->store('database_images', 'public');
                        $databaseData->images()->create([
                            'path' => Storage::url($path),
                            'filename' => $imageFile->getClientOriginalName(),
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => 'Data Database berhasil ditambahkan!',
                'database_data' => $databaseData->load('images')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, DatabaseData $databaseData)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'keterangan' => 'nullable|string',
            'database_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'relasi' => 'nullable|string',
            'existing_database_images.*' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $databaseData) {
                $databaseData->update($request->only([
                    'keterangan',
                    'relasi',
                ]));

                $existingImagePaths = $request->input('existing_database_images', []);
                foreach ($databaseData->images as $image) {
                    if (!in_array($image->path, $existingImagePaths)) {
                        Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                        $image->delete();
                    }
                }

                if ($request->hasFile('database_images')) {
                    foreach ($request->file('database_images') as $imageFile) {
                        $path = $imageFile->store('database_images', 'public');
                        $databaseData->images()->create([
                            'path' => Storage::url($path),
                            'filename' => $imageFile->getClientOriginalName(),
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => 'Data Database berhasil diperbarui!',
                'database_data' => $databaseData->load('images')
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Database.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(DatabaseData $databaseData)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        try {
            DB::transaction(function () use ($databaseData) {
                foreach ($databaseData->images as $image) {
                    Storage::disk('public')->delete(Str::after($image->path, '/storage/'));
                    $image->delete();
                }
                $databaseData->delete();
            });
            return response()->json(['success' => 'Data Database berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Database.', 'error' => $e->getMessage()], 500);
        }
    }
}
