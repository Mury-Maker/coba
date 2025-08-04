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
use Illuminate\Support\Facades\Auth;

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
            'database_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'relasi' => 'nullable|string',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $databaseData = null;

            // PERBAIKAN: Tangkap objek yang dikembalikan dari transaksi
            $databaseData = DB::transaction(function () use ($request, $useCase) {
                $newDatabaseData = $useCase->databaseData()->create([
                    'keterangan' => $request->keterangan,
                    'relasi' => $request->relasi,
                ]);

                if ($request->hasFile('database_images')) {
                    foreach ($request->file('database_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('database_images', 'public');
                            $newDatabaseData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }
                return $newDatabaseData; // Kembalikan objek yang baru dibuat
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
        $this->ensureAdminAccess();

        $request->validate([
            'keterangan' => 'nullable|string',
            'database_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'relasi' => 'nullable|string',
            'database_images_current.*' => 'nullable|string',
        ]);

        try {
            // PERBAIKAN: Tangkap objek yang dikembalikan dari transaksi
            $updatedDatabaseData = DB::transaction(function () use ($request, $databaseData) {
                $databaseData->update($request->only([
                    'keterangan',
                    'relasi',
                ]));

                $keptImagePaths = $request->input('database_images_current', []);

                foreach ($databaseData->images as $image) {
                    $storagePath = Str::after($image->path, '/storage/');
                    if (!in_array($image->path, $keptImagePaths) && !in_array($storagePath, $keptImagePaths)) {
                        Storage::disk('public')->delete($storagePath);
                        $image->delete();
                    }
                }

                if ($request->hasFile('database_images')) {
                    foreach ($request->file('database_images') as $imageFile) {
                        if ($imageFile->isValid()) {
                            $path = $imageFile->store('database_images', 'public');
                            $databaseData->images()->create([
                                'path' => Storage::url($path),
                                'filename' => $imageFile->getClientOriginalName(),
                            ]);
                        }
                    }
                }
                return $databaseData; // Kembalikan objek yang sudah diperbarui
            });

            return response()->json([
                'success' => 'Data Database berhasil diperbarui!',
                'database_data' => $updatedDatabaseData->load('images')
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
                $databaseData->delete();
            });
            return response()->json(['success' => 'Data Database berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Database: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Database.', 'error' => $e->getMessage()], 500);
        }
    }
}
