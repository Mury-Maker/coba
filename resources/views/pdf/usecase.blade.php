<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Usecase - {{ $menu->menu_nama }}</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px; 
        }
        h1, h2, h3 { 
            text-align: center; 
        }
        h2 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 30px;
            color: #34495e;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            table-layout: fixed; /* Mencegah tabel meluber */
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px 12px; 
            word-wrap: break-word; /* Memaksa teks yang panjang untuk memecah baris */
        }
        th { 
            background: #eee; 
            font-weight: bold; 
        }
        .empty-data {
            text-align: center;
            font-style: italic;
            color: #7f8c8d;
            padding: 20px;
        }
        @page {
            margin: 20mm;
        }
        @media print {
            thead {
                display: table-header-group; /* Menampilkan header tabel di setiap halaman */
            }
            tr {
                page-break-inside: avoid; /* Mencegah baris terpotong di tengah */
            }
        }
    </style>
</head>
<body>
    <h2>Use Case - {{ $menu->menu_nama }}</h2>

    <table>
        <thead>
            <tr>
                <th>Nama Proses</th>
                <th>Deskripsi Aksi</th>
                <th>Aktor</th>
                <th>Tujuan</th>
                <th>Kondisi Awal</th>
                <th>Kondisi Akhir</th>
                <th>Aksi Aktor</th>
                <th>Reaksi Sistem</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($useCases as $uc)
                <tr>
                    <td>{{ $uc->nama_proses ?? '-' }}</td>
                    <td>{{ $uc->deskripsi_aksi ?? '-' }}</td>
                    <td>{{ $uc->aktor ?? '-' }}</td>
                    <td>{{ $uc->tujuan ?? '-' }}</td>
                    <td>{{ $uc->kondisi_awal ?? '-' }}</td>
                    <td>{{ $uc->kondisi_akhir ?? '-' }}</td>
                    <td>{{ $uc->aksi_aktor ?? '-' }}</td>
                    <td>{{ $uc->reaksi_sistem ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-data">Tidak ada data use case yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>