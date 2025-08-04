<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        // Define the menu structure
        $menus = [
            'Dashboard' => ['icon' => 'fa-solid fa-home', 'children' => []],
            'Kesantrian' => [
                'icon' => 'fa-solid fa-user-graduate',
                'children' => [
                    'Kelas',
                    'Kamar',
                    'Santri',
                    'Tahfidz',
                    'Nadhoman'
                ]
            ],
            'Kepegawaian' => [
                'icon' => 'fa-solid fa-users',
                'children' => [
                    'Jabatan Pegawai',
                    'Pegawai'
                ]
            ],
            'Akademik' => [
                'icon' => 'fa-solid fa-book',
                'children' => [
                    'Tahun Ajaran',
                    'Semester',
                    'Pindah-Naik Kelas',
                    'Pindah Kamar',
                    'Kelulusan',
                    'Data Kitab',
                    'Pelajaran',
                    'Presensi',
                    'Data Hari Libur',
                    'Alumni' => [
                        'icon' => 'fa-solid fa-graduation-cap',
                        'children' => [
                            'Data Alumni',
                        ]
                    ]
                ]
            ],
            'Keuangan' => [
                'icon' => 'fa-solid fa-dollar-sign',
                'children' => [
                    'Pembayaran Santri',
                    'Setting Pembayaran' => [
                        'children' => [
                            'Akun Biaya',
                            'Pos Bayar',
                            'Jenis Bayar'
                        ]
                    ],
                    'Bukti Transfer Wali Murid',
                    'Limit Tarik Tabungan',
                    'Tabungan Santri',
                    'Kas & Bank' => [
                        'children' => [
                            'Saldo Awal',
                            'Kas Keluar',
                            'Kas Masuk'
                        ]
                    ],
                    'Penggajian' => [
                        'children' => [
                            'Setting Gaji',
                            'Slip Gaji'
                        ]
                    ]
                ]
            ],
            'Laporan' => [
                'icon' => 'fa-solid fa-chart-line',
                'children' => [
                    'Lap. Kesantrian' => [
                        'children' => [
                            'Rekap Tahfidz'
                        ]
                    ],
                    'Lap. Pembayaran' => [
                        'children' => [
                            'Per Kelas',
                            'Per Tanggal',
                            'Tagihan Santri',
                            'Rekap Pembayaran'
                        ]
                    ],
                    'Lap. Keuangan' => [
                        'children' => [
                            'Lap. Jurnal',
                            'Lap. Kas Tunai',
                            'Lap. Kas Bank'
                        ]
                    ]
                ]
            ],
            'Pengaturan' => [
                'icon' => 'fa-solid fa-cog',
                'children' => [
                    'Sekolah',
                    'Informasi',
                    'Manajemen Pengguna',
                    'Logs Transaksi'
                ]
            ]
        ];

        $this->seedMenus($epesantrenCategory->id, $menus);
    }

    private function seedMenus(int $categoryId, array $menus, int $parent_id = 0): void
    {
        $order = 1;
        foreach ($menus as $menuName => $menuData) {
            // Check if it's a menu with children or a simple string
            if (is_array($menuData)) {
                $hasChildren = isset($menuData['children']) && !empty($menuData['children']);
                $icon = $menuData['icon'] ?? null;
            } else {
                $menuName = $menuData;
                $menuData = [];
                $hasChildren = false;
                $icon = 'fa-regular fa-circle'; // Default icon for child menus
            }

            // Create or update the menu item
            $menu = NavMenu::firstOrCreate(
                ['category_id' => $categoryId, 'menu_nama' => $menuName, 'menu_child' => $parent_id],
                [
                    'menu_link' => Str::slug($menuName),
                    'menu_icon' => $icon,
                    'menu_order' => $order++,
                    'menu_status' => $hasChildren ? 0 : 1 // 0=folder, 1=use case
                ]
            );

            // Recursively seed child menus if they exist
            if ($hasChildren) {
                $this->seedMenus($categoryId, $menuData['children'], $menu->menu_id);
            }
        }
    }
}
