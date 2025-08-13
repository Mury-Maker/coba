<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lengkap Use Case</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .container {
            padding: 20px;
        }

        h1,
        h2,
        h3 {
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
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .section {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Detail Semua Use Case</h1>
        <h2>Menu: {{ $menu->menu_nama }}</h2>

        @foreach ($useCases as $useCase)
            <div class="section">
                <h3>Detail Use Case: {{ $useCase->nama_proses }}</h3>
                <table>
                    <tbody>
                        <tr>
                            <th>Nama Proses</th>
                            <td>{{ $useCase->nama_proses }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi Aksi</th>
                            <td>{{ $useCase->deskripsi_aksi }}</td>
                        </tr>
                        <tr>
                            <th>Aktor</th>
                            <td>{{ $useCase->aktor }}</td>
                        </tr>
                        <tr>
                            <th>Tujuan</th>
                            <td>{{ $useCase->tujuan }}</td>
                        </tr>
                        <tr>
                            <th>Kondisi Awal</th>
                            <td>{{ $useCase->kondisi_awal }}</td>
                        </tr>
                        <tr>
                            <th>Kondisi Akhir</th>
                            <td>{{ $useCase->kondisi_akhir }}</td>
                        </tr>
                        <tr>
                            <th>Aksi Aktor</th>
                            <td>{{ $useCase->aksi_aktor }}</td>
                        </tr>
                        <tr>
                            <th>Reaksi Sistem</th>
                            <td>{{ $useCase->reaksi_sistem }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($useCase->reportData->isNotEmpty())
                <div class="section">
                    <h3>Tabel Report</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Aktor</th>
                                <th>Nama Report</th>
                                <th>keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($useCase->reportData as $report)
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

            @if ($useCase->databaseData->isNotEmpty())
                <div class="section">
                    <h3>Tabel Database</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Keterangan</th>
                                <th>Relasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($useCase->databaseData as $database)
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

            @if ($useCase->uatData->isNotEmpty())
                <div class="section">
                    <h3>Tabel UAT</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Proses</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($useCase->uatData as $uat)
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

            <hr style="border-top: 1px dashed #ccc; margin: 40px 0;">
        @endforeach
    </div>
</body>

</html>
