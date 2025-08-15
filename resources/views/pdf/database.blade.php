<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data UAT</title>
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
            padding-top: 25px; /* jarak dari garis */
            padding-bottom: 25px;
        }

        .image-gallery img {
            max-width: 80%;
            height: auto;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<h1>Database - {{ $usecase->nama_proses }}</h2>

{{-- Bagian Tabel Utama --}}
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Tabel</th>
            <th>Relasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($usecase->databaseData as $index => $database)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $database->keterangan }}</td>
                <td>{{ $database->relasi }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Bagian Lampiran Gambar per ID --}}
@foreach($usecase->databaseData as $database)
    @if($database->images->count() > 0)
        <div class="page-break"></div>
        <h2>Lampiran Gambar - ID {{ $database->id_database }}</h2>
        
        <div class="image-gallery">
            @foreach($database->images as $image)
                @php
                    $fileName = basename($image->path);
                    $path = public_path('storage/database_images/' . $fileName);
                    $base64 = null;
                    if (file_exists($path)) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $mime = $ext === 'jpg' ? 'jpeg' : $ext;
                        $base64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    }
                @endphp
                
                @if($base64)
                    <img src="{{ $base64 }}" alt="Gambar database">
                @endif
            @endforeach
        </div>
    @endif
@endforeach

</body>
</html>