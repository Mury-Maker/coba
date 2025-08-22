<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Use Case Lengkap</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
        }
        .container { 
            padding: 20px;
        }
        h1, h2, h3 { 
            color: #333; 
            margin-top: 20px; 
            margin-bottom: 10px;
        }
        h1 { 
            font-size: 24px; 
            text-align: center;
        }
        h2 { 
            font-size: 18px; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 5px;
        }
        h3 { 
            font-size: 14px; 
            color: #555;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            table-layout: fixed; /* Mencegah tabel meluber */
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left;
            word-wrap: break-word; /* Memecah teks panjang */
        }
        th { 
            background-color: #f2f2f2; 
        }
        .section { 
            margin-bottom: 30px; 
        }

        /* Mengatur lebar kolom agar judul tidak terlalu besar */
        .detail-table th:first-child {
            width: 25%;
        }
        .detail-table td:last-child {
            width: 75%;
        }

        /* Mengatur lebar kolom "No" pada semua tabel data */
        .data-table th:first-child {
            width: 5%;
        }
        .data-table th:nth-child(2) {
            width: 20%;
        }
        .data-table th:nth-child(3) {
            width: 45%;
        }
        .data-table th:nth-child(4) {
            width: 30%;
        }

        /* Aturan khusus untuk media cetak */
        @media print {
            body { 
                margin: 0;
            }
            .container { 
                padding: 0;
            }
            
            @page {
                margin: 20mm; /* Atur margin kertas */
            }

            /* Mencegah setiap bagian (section) terpotong di tengah halaman */
            .section {
                page-break-inside: avoid;
            }
            
            /* Memastikan header tabel muncul di setiap halaman */
            thead {
                display: table-header-group;
            }
            
            /* Memastikan baris tabel tidak terpotong */
            tr {
                page-break-inside: avoid;
            }

            /* Memastikan halaman baru untuk setiap bagian jika diperlukan */
            .section:not(:first-of-type) {
                page-break-before: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detail Use Case Lengkap</h1>
        <h2>Use Case: {{ $singleUseCase->nama_proses }}</h2>

        <div class="section">
            <h3>Detail Umum</h3>
            <table class="detail-table">
                <tbody>
                    <tr><th>Nama Proses</th><td>{{ $singleUseCase->nama_proses }}</td></tr>
                    <tr><th>Deskripsi Aksi</th><td>{{ $singleUseCase->deskripsi_aksi }}</td></tr>
                    <tr><th>Aktor</th><td>{{ $singleUseCase->aktor }}</td></tr>
                    <tr><th>Tujuan</th><td>{{ $singleUseCase->tujuan }}</td></tr>
                    <tr><th>Kondisi Awal</th><td>{{ $singleUseCase->kondisi_awal }}</td></tr>
                    <tr><th>Kondisi Akhir</th><td>{{ $singleUseCase->kondisi_akhir }}</td></td></tr>
                    <tr><th>Aksi Aktor</th><td>{!! $singleUseCase->aksi_aktor !!}</td></tr>
                    <tr><th>Reaksi Sistem</th><td>{!! $singleUseCase->reaksi_sistem !!}</td></tr>
                </tbody>
            </table>
        </div>

        @if ($singleUseCase->reportData->isNotEmpty())
            <div class="section">
                <h3>Tabel Report</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>No</th><th>Aktor</th><th>Nama Report</th><th>keterangan</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($singleUseCase->reportData as $report)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $report->aktor }}</td>
                                <td>{{ $report->nama_report }}</td>
                                <td>{{ $report->keterangan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($singleUseCase->databaseData->isNotEmpty())
            <div class="section">
                <h3>Tabel Database</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>No</th><th>Nama Tabel</th><th>Relasi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($singleUseCase->databaseData as $database)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $database->keterangan }}</td>
                                <td>{{ $database->relasi }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($singleUseCase->uatData->isNotEmpty())
            <div class="section">
                <h3>Tabel UAT</h3>
                <table class="data-table">
                    <thead>
                        <tr><th>No</th><th>Nama Proses</th><th>Keterangan</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($singleUseCase->uatData as $uat)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $uat->nama_proses_usecase }}</td>
                                <td>{{ $uat->keterangan_uat }}</td>
                                <td>{{ $uat->status_uat }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>