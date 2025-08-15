<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data UAT</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; margin: 20px; }
        h1, h2, h3 { text-align: center; }
        h1 { margin-bottom: 20px; color: #2c3e50; }
        h2 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 30px;
            color: #34495e;
        }
        h3 { color: #7f8c8d; font-weight: normal; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td {
            border: 1px solid #000;
            padding: 12px; /* Ditingkatkan dari 10px */
            vertical-align: top;
            text-align: left;
        }
        table th { background-color: #f2f2f2; font-weight: bold; }
        .empty-data {
            text-align: center;
            font-style: italic;
            color: #7f8c8d;
            padding: 20px;
        }
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px; /* Ditingkatkan dari 10px */
            justify-content: center; /* Gambar di tengah */
            padding-top: 25px;
            padding-bottom: 25px;
        }
        .image-gallery img {
            max-width: 80%;
            height: auto;
            object-fit: contain; /* Menggunakan contain agar gambar tidak terpotong */
            border: 1px solid #ccc;
            border-radius: 5px;
            display: block;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<h1>UAT: {{ $usecase->nama_proses ?? '-' }}</h1>

{{-- Bagian Tabel Utama --}}
<h2>Data UAT</h2>
{{-- Menggunakan @forelse untuk menangani data kosong --}}
@forelse($usecase->uatData as $index => $uat)
    @if ($loop->first)
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
    @endif
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $uat->nama_proses_usecase ?? '-' }}</td>
                <td>{{ $uat->keterangan_uat ?? '-' }}</td>
                <td>{{ $uat->status_uat ?? '-' }}</td>
            </tr>
    @if ($loop->last)
        </tbody>
    </table>
    @endif
@empty
    {{-- Tampilkan pesan ini jika $usecase->uatData kosong --}}
    <p class="empty-data">Tidak ada data UAT yang ditemukan.</p>
@endforelse

{{-- Bagian Lampiran Gambar per ID --}}
@foreach($usecase->uatData as $uat)
    {{-- Tampilkan header hanya jika ada gambar yang terkait --}}
    @if($uat->images->count() > 0)
        @if(!$loop->first)
        <div class="page-break"></div>
        @endif
        <h2>Lampiran Gambar - ID {{ $uat->id_uat }} ({{ $uat->nama_proses_usecase ?? '-' }})</h2>
        
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