<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\ReportData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class ReportDataController extends Controller
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
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $useCase = UseCase::findOrFail($request->use_case_id);
            $reportData = $useCase->reportData()->create($request->all());

            return response()->json(['success' => 'Data Report berhasil ditambahkan!', 'report_data' => $reportData], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, ReportData $reportData)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'aktor' => 'required|string|max:255',
            'nama_report' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $reportData->update($request->all());
            return response()->json(['success' => 'Data Report berhasil diperbarui!', 'report_data' => $reportData]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data Report.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(ReportData $reportData)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        try {
            $reportData->delete();
            return response()->json(['success' => 'Data Report berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data Report: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data Report.', 'error' => $e->getMessage()], 500);
        }
    }
}
