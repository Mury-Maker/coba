<?php

namespace App\Http\Controllers\UseCaseData;

use App\Http\Controllers\Controller;
use App\Models\UseCase;
use App\Models\NavMenu;
use App\Models\Report; // Tambahkan ini
use App\Models\Database; // Tambahkan ini
use App\Models\Uat; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class UseCaseController extends Controller
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
            'menu_id' => 'required|exists:navmenu,menu_id',
            'nama_proses' => 'required|string|max:255',
            'deskripsi_aksi' => 'nullable|string',
            'aktor' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string',
            'kondisi_awal' => 'nullable|string',
            'kondisi_akhir' => 'nullable|string',
            'aksi_aktor' => 'nullable|string',
            'reaksi_sistem' => 'nullable|string',
        ]);

        try {
            $useCase = null;
            $useCase = DB::transaction(function () use ($request) {
                $lastUseCaseForMenu = UseCase::where('menu_id', $request->menu_id)
                                             ->orderBy('id', 'desc')
                                             ->first();

                $useCase = UseCase::create([
                    'menu_id' => $request->menu_id,
                    'nama_proses' => $request->nama_proses,
                    'deskripsi_aksi' => $request->deskripsi_aksi,
                    'aktor' => $request->aktor,
                    'tujuan' => $request->tujuan,
                    'kondisi_awal' => $request->kondisi_awal,
                    'kondisi_akhir' => $request->kondisi_akhir,
                    'aksi_aktor' => $request->aksi_aktor,
                    'reaksi_sistem' => $request->reaksi_sistem,
                ]);
                return $useCase;
            });

            return response()->json([
                'success' => 'Data tindakan berhasil ditambahkan!',
                'use_case_slug' => Str::slug($useCase->nama_proses),
                'menu_id' => $useCase->menu_id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, UseCase $useCase)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        $request->validate([
            'nama_proses' => 'required|string|max:255',
            'deskripsi_aksi' => 'nullable|string',
            'aktor' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string',
            'kondisi_awal' => 'nullable|string',
            'kondisi_akhir' => 'nullable|string',
            'aksi_aktor' => 'nullable|string',
            'reaksi_sistem' => 'nullable|string',
        ]);

        try {
            $useCase->update($request->all());
            return response()->json([
                'success' => 'Data tindakan berhasil diperbarui!',
                'use_case_slug' => Str::slug($useCase->nama_proses),
                'menu_id' => $useCase->menu_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(UseCase $useCase)
    {
        $this->ensureAdminAccess(); // Verifikasi akses Admin

        try {
            DB::transaction(function () use ($useCase) {
                $useCase->delete();
            });
            return response()->json(['success' => 'Data tindakan berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus tindakan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus data tindakan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function cetakPDF($menu_id)
    {
        $this->ensureAdminAccess();
    
        $useCases = UseCase::where('menu_id', $menu_id)->get();
        $menu = NavMenu::where('menu_id', $menu_id)->first();
    
        $pdf = PDF::loadView('pdf.usecase', [
            'useCases' => $useCases,
            'menu' => $menu,
        ])->setPaper('a4', 'landscape'); // Tambahkan ini
    
        return $pdf->stream('Usecase_' . Str::slug($menu->menu_nama) . '.pdf');
    }
    
    public function printSingle($id)
    {
        $singleUseCase = Usecase::findOrFail($id);

        // Buat PDF
        $pdf = Pdf::loadView('pdf.print-single-usecase', compact('singleUseCase'))
                  ->setPaper('A4', 'portrait');

        // Tampilkan langsung di browser
        return $pdf->stream('usecase-'.$singleUseCase->id.'.pdf');
    }

    public function printSingleComplete($id)
    {
        $this->ensureAdminAccess();

        // Ambil data use case berdasarkan ID, dengan memuat data relasi
        $singleUseCase = UseCase::with(['reportData', 'databaseData', 'uatData'])
                                ->findOrFail($id);

        // Load view 'pdf.print-single-complete' dengan data yang sudah diambil
        $pdf = PDF::loadView('pdf.print-single-complete', [
            'singleUseCase' => $singleUseCase,
        ]);

        return $pdf->stream('Usecase_Detail_' . Str::slug($singleUseCase->nama_proses) . '.pdf');
    }

    /**
     * Mencetak PDF untuk semua use case dan data yang berelasi (reports, databases, uats).
     *
     * @param int $menu_id
     * @return \Illuminate\Http\Response
     */
    public function cetakPDFComplete($menu_id)
    {
        $this->ensureAdminAccess();
    
        // Ambil data use case beserta relasinya
        // Gunakan nama relasi yang benar: 'reportData', 'databaseData', 'uatData'
        $useCases = UseCase::with(['reportData', 'databaseData', 'uatData'])
                           ->where('menu_id', $menu_id)
                           ->get();
        
        $menu = NavMenu::where('menu_id', $menu_id)->firstOrFail();
    
        // Load view 'pdf.usecase_complete' dengan data yang sudah diambil
        $pdf = PDF::loadView('pdf.usecase-complete', [
            'useCases' => $useCases,
            'menu' => $menu,
        ]);
    
        return $pdf->stream('Usecase_Lengkap_' . Str::slug($menu->menu_nama) . '.pdf');
    }
}
