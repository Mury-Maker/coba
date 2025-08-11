<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report</title>
    <style>
        /* Gaya dasar untuk dokumen */
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 14px;
            margin: 20px;
        }

        /* Gaya untuk judul halaman */
        h1 { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #2c3e50;
        }
        
        /* Gaya untuk subjudul */
        h2 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 30px;
            color: #34495e;
        }

        /* Gaya untuk tabel */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        table th, table td {
            border: 1px solid #000;
            padding: 10px; /* Padding lebih besar untuk keterbacaan */
            vertical-align: top;
            text-align: left;
        }
        table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        
        /* Gaya untuk kontainer gambar */
        .image-gallery {
            display: flex;
            flex-wrap: wrap; /* Memungkinkan gambar untuk pindah ke baris baru */
            gap: 10px; /* Jarak antar gambar */
            padding: 5px;
        }
        .image-gallery img {
            max-width: 150px; /* Lebar maksimal gambar */
            height: auto; /* Ketinggian otomatis disesuaikan */
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }
    </style>
</head>
<body>

    <h1>Laporan: {{ $usecase->nama_proses ?? 'Nama Proses Tidak Tersedia' }}</h1>
    <h2>Data Laporan</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Aktor</th>
                <th style="width: 20%;">Nama Laporan</th>
                <th style="width: 30%;">Keterangan</th>
                <th style="width: 30%;">Gambar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usecase->reportData as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $report->aktor ?? '-' }}</td>
                    <td>{{ $report->nama_report ?? '-' }}</td>
                    <td>{{ $report->keterangan ?? '-' }}</td>
                    <td>
                        <div class="image-gallery">
                            @foreach($report->images as $image)
                                @php
                                    $fileName = basename($image->path);
                                    // Memastikan jalur file gambar sesuai dengan data laporan
                                    $path = public_path('storage/report_images/' . $fileName);
                                    $base64 = null;
                                    if (file_exists($path)) {
                                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                                        $mime = $ext === 'jpg' ? 'jpeg' : $ext;
                                        $base64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                                    }
                                @endphp
                                
                                @if($base64)
                                    <img src="{{ $base64 }}" alt="Gambar Laporan">
                                @endif
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>