<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Tambahkan ini

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan kategori 'epesantren' ada. Jika tidak ada, buat.
        $epesantrenCategory = Category::firstOrCreate(
            ['slug' => 'epesantren'],
            ['name' => 'ePesantren']
        );

        // Periksa apakah menu 'Beranda ePesantren' sudah ada untuk kategori ini
        $homeMenu = NavMenu::where('category_id', $epesantrenCategory->id)
                            ->where('menu_nama', 'Beranda ePesantren')
                            ->first();

        // Jika belum ada, buat menu 'Beranda ePesantren' dan UseCase default-nya
        if (!$homeMenu) {
            DB::transaction(function () use ($epesantrenCategory) {
                $homeMenu = NavMenu::create([
                    'category_id' => $epesantrenCategory->id,
                    'menu_nama' => 'Beranda ePesantren',
                    'menu_link' => Str::slug('Beranda ePesantren'),
                    'menu_icon' => 'fa-solid fa-home',
                    'menu_child' => 0,
                    'menu_order' => 0,
                    'menu_status' => 1, // Ini akan punya daftar use case
                ]);

                // Buat UseCase default "Informasi Umum"
                UseCase::create([
                    'menu_id' => $homeMenu->menu_id,
                    'usecase_id' => 'INFO-BERANDA',
                    'nama_proses' => 'Informasi Umum',
                    'deskripsi_aksi' => '# Informasi Umum Kategori ePesantren' . "\n\nIni adalah informasi pengantar untuk kategori ePesantren. Anda dapat menambahkan tindakan-tindakan lain (use cases) di sini.",
                    'aktor' => 'Sistem',
                    'tujuan' => 'Memberikan gambaran umum kategori.',
                    'kondisi_awal' => 'Pengguna mengakses halaman beranda kategori.',
                    'kondisi_akhir' => 'Informasi umum ditampilkan.',
                    'aksi_reaksi' => 'Pengguna membaca konten.',
                    'reaksi_sistem' => 'Sistem menyajikan informasi.',
                ]);
            });
        }
    }
}
