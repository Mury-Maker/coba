<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminSekolahSeeder extends Seeder
{
    /**
     * Jalankan seed database.
     *
     * @return void
     */
    public function run(): void
    {
        // Pastikan kategori 'Admin Sekolah' ada. Jika tidak ada, buat.
        $adminSekolahCategory = Category::firstOrCreate(
            ['slug' => 'admin-sekolah'],
            ['name' => 'Admin Sekolah']
        );

        // Definisikan struktur menu untuk Admin Sekolah
        $menus = [
            'Dashboard' => [
                'icon' => 'fa-solid fa-tachometer-alt',
                'children' => []
            ],
            'Manajemen Pengguna' => [
                'icon' => 'fa-solid fa-users-cog',
                'children' => [
                    'Manajemen Siswa' => ['icon' => 'fa-solid fa-user-graduate', 'children' => []],
                    'Manajemen Guru' => ['icon' => 'fa-solid fa-chalkboard-teacher', 'children' => []],
                    'Manajemen Staf' => ['icon' => 'fa-solid fa-user-tie', 'children' => []]
                ]
            ],
            'Akademik' => [
                'icon' => 'fa-solid fa-book-open',
                'children' => [
                    'Mata Pelajaran' => ['icon' => 'fa-solid fa-book', 'children' => []],
                    'Kelas' => ['icon' => 'fa-solid fa-chalkboard', 'children' => []],
                    'Jadwal Pelajaran' => ['icon' => 'fa-solid fa-calendar-alt', 'children' => []],
                    'Nilai Siswa' => ['icon' => 'fa-solid fa-award', 'children' => []]
                ]
            ],
            'Keuangan' => [
                'icon' => 'fa-solid fa-dollar-sign',
                'children' => [
                    'Pembayaran SPP' => ['icon' => 'fa-solid fa-money-bill-wave', 'children' => []],
                    'Pengeluaran' => ['icon' => 'fa-solid fa-cash-register', 'children' => []],
                ]
            ],
            'Laporan' => [
                'icon' => 'fa-solid fa-chart-line',
                'children' => [
                    'Laporan Akademik' => ['icon' => 'fa-solid fa-chart-bar'],
                    'Laporan Keuangan' => ['icon' => 'fa-solid fa-chart-pie'],
                ]
            ],
            'Pengaturan' => [
                'icon' => 'fa-solid fa-cogs',
                'children' => [
                    'Profil Sekolah' => ['icon' => 'fa-solid fa-school'],
                    'Manajemen Role' => ['icon' => 'fa-solid fa-user-tag'],
                ]
                ],
            'Daftar Tabel Admin Sekolah' => ['icon' => 'fa-solid fa-table', 'children' => []],
        ];

        // Memanggil fungsi untuk menanam menu secara rekursif
        $this->seedMenus($adminSekolahCategory->id, $menus);
    }

    /**
     * Fungsi helper untuk menanam menu secara rekursif.
     *
     * @param int $categoryId
     * @param array $menus
     * @param int $parentId
     * @return void
     */
    private function seedMenus(int $categoryId, array $menus, int $parentId = 0): void
    {
        $order = 1;
        foreach ($menus as $menuName => $menuData) {
            // Tentukan apakah menu memiliki anak atau hanya string
            if (is_array($menuData)) {
                $hasChildren = isset($menuData['children']) && !empty($menuData['children']);
                $icon = $menuData['icon'] ?? null;
            } else {
                $menuName = $menuData;
                $menuData = [];
                $hasChildren = false;
                $icon = 'fa-regular fa-circle'; // Ikon default untuk submenu
            }

            // Buat atau perbarui item menu
            $menu = NavMenu::firstOrCreate(
                ['category_id' => $categoryId, 'menu_nama' => $menuName, 'menu_child' => $parentId],
                [
                    'menu_link' => Str::slug($menuName),
                    'menu_icon' => $icon,
                    'menu_order' => $order++,
                    'menu_status' => $hasChildren ? 0 : 1 // 0=folder, 1=use case
                ]
            );

            // Jika menu adalah use case (menu_status == 1), panggil fungsi seed use case
            if ($menu->menu_status == 1) {
                $this->seedUseCases($menu->menu_id);
            }

            // Tanam submenu secara rekursif jika ada
            if ($hasChildren) {
                $this->seedMenus($categoryId, $menuData['children'], $menu->menu_id);
            }
        }
    }

    /**
     * Fungsi helper untuk menanam data use case berdasarkan menu_id.
     *
     * @param int $menuId
     * @return void
     */
    private function seedUseCases(int $menuId): void
    {
        $menu = NavMenu::find($menuId);
        if (!$menu) {
            return;
        }

        $menuName = $menu->menu_nama;
        $useCasesData = [];

        switch ($menuName) {
            case 'Manajemen Siswa':
                $useCasesData = [
                    [
                        'nama_proses' => 'Menambahkan Siswa Baru',
                        'deskripsi_aksi' => 'Admin Sekolah dapat menambahkan data siswa baru ke dalam sistem.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Mendata siswa baru untuk keperluan administrasi dan akademik.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Siswa.',
                        'kondisi_akhir' => 'Data siswa baru tersimpan di database dan ditampilkan di daftar siswa.',
                        'aksi_aktor' => '1. Admin menekan tombol "Tambah Siswa". <br> 2. Admin mengisi formulir data siswa (nama, NIS, kelas, dll.). <br> 3. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir kosong untuk data siswa. <br> 2. Sistem memvalidasi input. <br> 3. Sistem menyimpan data ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                    [
                        'nama_proses' => 'Mengubah Data Siswa',
                        'deskripsi_aksi' => 'Admin Sekolah dapat memperbarui data siswa yang sudah ada.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Memperbarui data siswa jika ada perubahan (misalnya, alamat, kelas, dll.).',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Siswa. Data siswa yang akan diubah sudah ada.',
                        'kondisi_akhir' => 'Data siswa berhasil diperbarui di database dan ditampilkan di daftar siswa.',
                        'aksi_aktor' => '1. Admin memilih siswa dari daftar. <br> 2. Admin menekan tombol "Edit". <br> 3. Admin mengubah data yang diperlukan. <br> 4. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir dengan data siswa yang dipilih. <br> 2. Sistem memvalidasi input yang diubah. <br> 3. Sistem menyimpan perubahan ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                    [
                        'nama_proses' => 'Menghapus Siswa',
                        'deskripsi_aksi' => 'Admin Sekolah dapat menghapus data siswa dari sistem.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Membersihkan data siswa yang tidak lagi diperlukan, misalnya karena lulus atau pindah.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Siswa. Data siswa yang akan dihapus sudah ada.',
                        'kondisi_akhir' => 'Data siswa berhasil dihapus dari database.',
                        'aksi_aktor' => '1. Admin memilih siswa dari daftar. <br> 2. Admin menekan tombol "Hapus". <br> 3. Admin mengkonfirmasi penghapusan.',
                        'reaksi_sistem' => '1. Sistem menampilkan dialog konfirmasi penghapusan. <br> 2. Sistem menghapus data siswa dari database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                ];
                break;
            case 'Manajemen Guru':
                $useCasesData = [
                    [
                        'nama_proses' => 'Menambahkan Guru Baru',
                        'deskripsi_aksi' => 'Admin Sekolah dapat menambahkan data guru baru ke dalam sistem.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Mendata guru baru untuk keperluan administrasi dan akademik.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Guru.',
                        'kondisi_akhir' => 'Data guru baru tersimpan di database dan ditampilkan di daftar guru.',
                        'aksi_aktor' => '1. Admin menekan tombol "Tambah Guru". <br> 2. Admin mengisi formulir data guru. <br> 3. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir kosong untuk data guru. <br> 2. Sistem memvalidasi input. <br> 3. Sistem menyimpan data ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                    [
                        'nama_proses' => 'Mengubah Data Guru',
                        'deskripsi_aksi' => 'Admin Sekolah dapat memperbarui data guru yang sudah ada.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Memperbarui data guru jika ada perubahan.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Guru. Data guru yang akan diubah sudah ada.',
                        'kondisi_akhir' => 'Data guru berhasil diperbarui di database.',
                        'aksi_aktor' => '1. Admin memilih guru dari daftar. <br> 2. Admin menekan tombol "Edit". <br> 3. Admin mengubah data yang diperlukan. <br> 4. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir dengan data guru yang dipilih. <br> 2. Sistem memvalidasi input yang diubah. <br> 3. Sistem menyimpan perubahan ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                    [
                        'nama_proses' => 'Menghapus Guru',
                        'deskripsi_aksi' => 'Admin Sekolah dapat menghapus data guru dari sistem.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Menghapus data guru yang sudah tidak mengajar di sekolah.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Manajemen Guru. Data guru yang akan dihapus sudah ada.',
                        'kondisi_akhir' => 'Data guru berhasil dihapus dari database.',
                        'aksi_aktor' => '1. Admin memilih guru dari daftar. <br> 2. Admin menekan tombol "Hapus". <br> 3. Admin mengkonfirmasi penghapusan.',
                        'reaksi_sistem' => '1. Sistem menampilkan dialog konfirmasi penghapusan. <br> 2. Sistem menghapus data guru dari database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                ];
                break;
            case 'Mata Pelajaran':
                $useCasesData = [
                    [
                        'nama_proses' => 'Menambahkan Mata Pelajaran Baru',
                        'deskripsi_aksi' => 'Admin Sekolah dapat menambahkan data mata pelajaran baru ke dalam sistem.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Mendata mata pelajaran yang diajarkan di sekolah.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Mata Pelajaran.',
                        'kondisi_akhir' => 'Data mata pelajaran baru tersimpan di database dan ditampilkan di daftar mata pelajaran.',
                        'aksi_aktor' => '1. Admin menekan tombol "Tambah Mata Pelajaran". <br> 2. Admin mengisi nama mata pelajaran. <br> 3. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir untuk data mata pelajaran. <br> 2. Sistem memvalidasi input. <br> 3. Sistem menyimpan data ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                    [
                        'nama_proses' => 'Mengubah Data Mata Pelajaran',
                        'deskripsi_aksi' => 'Admin Sekolah dapat memperbarui data mata pelajaran yang sudah ada.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Memperbarui nama mata pelajaran.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Mata Pelajaran. Data mata pelajaran yang akan diubah sudah ada.',
                        'kondisi_akhir' => 'Data mata pelajaran berhasil diperbarui di database.',
                        'aksi_aktor' => '1. Admin memilih mata pelajaran dari daftar. <br> 2. Admin menekan tombol "Edit". <br> 3. Admin mengubah nama mata pelajaran. <br> 4. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir dengan data mata pelajaran yang dipilih. <br> 2. Sistem memvalidasi input. <br> 3. Sistem menyimpan perubahan ke database dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                ];
                break;
            case 'Pembayaran SPP':
                $useCasesData = [
                    [
                        'nama_proses' => 'Merekam Pembayaran SPP',
                        'deskripsi_aksi' => 'Admin Sekolah dapat merekam pembayaran SPP yang dilakukan oleh siswa.',
                        'aktor' => 'Admin Sekolah',
                        'tujuan' => 'Mendata transaksi pembayaran SPP untuk setiap siswa.',
                        'kondisi_awal' => 'Admin sudah login dan berada di halaman Pembayaran SPP.',
                        'kondisi_akhir' => 'Transaksi pembayaran SPP tersimpan di database dan status pembayaran siswa diperbarui.',
                        'aksi_aktor' => '1. Admin memilih siswa. <br> 2. Admin mengisi detail pembayaran (bulan, jumlah, dll.). <br> 3. Admin menekan tombol "Simpan".',
                        'reaksi_sistem' => '1. Sistem menampilkan formulir pembayaran. <br> 2. Sistem memvalidasi input. <br> 3. Sistem menyimpan transaksi, memperbarui status siswa, dan menampilkan pesan sukses.',
                        'menu_id' => $menuId,
                    ],
                ];
                break;
            // Tambahkan case untuk menu lainnya di sini
        }

        foreach ($useCasesData as $data) {
            UseCase::firstOrCreate(
                ['nama_proses' => $data['nama_proses'], 'menu_id' => $menuId],
                $data
            );
        }
    }
}