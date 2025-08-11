<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>UAT Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; margin: 20px; }
        h1 { text-align: center; margin-bottom: 20px; color: #2c3e50; }
        h2 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 30px;
            color: #34495e;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
            text-align: left;
        }
        table th { background-color: #f2f2f2; font-weight: bold; }
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 5px;
        }
        .image-gallery img {
            width: 150px; 
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<h1>UAT Report: {{ $usecase->nama_proses ?? '-' }}</h1>

{{-- Bagian Tabel Utama --}}
<h2>Data UAT</h2>
<table>
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 20%;">Nama Proses</th>
            <th style="width: 25%;">Keterangan</th>
            <th style="width: 10%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usecase->uatData as $index => $uat)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $uat->nama_proses_usecase ?? '-' }}</td>
                <td>{{ $uat->keterangan_uat ?? '-' }}</td>
                <td>{{ $uat->status_uat ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Bagian Lampiran Gambar per ID --}}
@foreach($usecase->uatData as $uat)
    @if($uat->images->count() > 0)
        <div class="page-break"></div>
        <h2>Lampiran Gambar - ID {{ $uat->id }} ({{ $uat->nama_proses_usecase ?? '-' }})</h2>
        <div class="image-gallery">
            @foreach($uat->images as $image)
                @php
                    $fileName = basename($image->path);
                    $path = public_path('storage/uat_images/' . $fileName);
                    $base64 = null;
                    if (file_exists($path)) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $mime = $ext === 'jpg' ? 'jpeg' : $ext;
                        $base64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    }
                @endphp
                
                @if($base64)
                    <img src="{{ $base64 }}" alt="Gambar UAT">
                @endif
            @endforeach
        </div>
    @endif
@endforeach

</body>
</html>
