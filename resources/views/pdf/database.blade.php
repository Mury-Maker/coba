<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1 { margin-bottom: 20px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td {
            border: 1px solid #000;
            padding: 8px; /* Padding lebih besar */
            vertical-align: top;
        }
        table th { background-color: #f2f2f2; }
        
        /* Gaya baru untuk kontainer gambar */
        .image-gallery {
            display: flex;
            flex-wrap: wrap; /* Memungkinkan gambar untuk pindah ke baris baru */
            gap: 10px; /* Jarak antar gambar */
            padding: 5px;
        }
        .image-gallery img {
            width: 150px; /* Ukuran gambar default yang lebih besar */
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <h2>Database - {{ $usecase->nama_proses }}</h2>

    <h4>Data Database</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Keterangan</th>
                <th>Relasi</th>
                <th>Gambar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usecase->databaseData as $index => $database)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $database->keterangan }}</td>
                    <td>{{ $database->relasi }}</td>
                    <td>
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
                                    <img src="{{ $base64 }}" alt="Gambar Database">
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