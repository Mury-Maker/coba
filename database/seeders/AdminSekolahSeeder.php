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
                    'Laporan Akademik',
                    'Laporan Keuangan',
                ]
            ],
            'Pengaturan' => [
                'icon' => 'fa-solid fa-cogs',
                'children' => [
                    'Profil Sekolah',
                    'Manajemen Role',
                ]
            ]
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

            // Tanam submenu secara rekursif jika ada
            if ($hasChildren) {
                $this->seedMenus($categoryId, $menuData['children'], $menu->menu_id);
            }
        }
    }
}
