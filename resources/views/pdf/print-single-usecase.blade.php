<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Detail Usecase</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            table-layout: fixed; /* Penting untuk mencegah tabel meluber */
        }
        td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            vertical-align: top;
            word-wrap: break-word; /* Memecah teks yang panjang */
        }
        .title { 
            font-weight: bold; 
            background-color: #f2f2f2; 
            width: 25%; /* Mengatur lebar kolom judul menjadi 25% */
        }
        .content {
            width: 75%; /* Memberikan sisa lebar untuk kolom konten */
        }

        /* Aturan khusus untuk media cetak */
        @media print {
            @page {
                margin: 20mm; /* Atur margin halaman */
            }
            body { 
                padding: 0; 
                margin: 0;
            }
            table {
                page-break-inside: avoid; /* Mencegah tabel terpotong di tengah */
            }
            tr {
                page-break-inside: avoid; /* Mencegah baris terpotong di tengah */
            }
        }
    </style>
</head>
<body onload="window.print()">
    <h2>Detail Usecase</h2>
    <table>
        <tr><td class="title">ID Usecase</td><td class="content">UC - {{ $singleUseCase->id }}</td></tr>
        <tr><td class="title">Nama Proses</td><td class="content">{{ $singleUseCase->nama_proses }}</td></tr>
        <tr><td class="title">Deskripsi Aksi</td><td class="content">{!! $singleUseCase->deskripsi_aksi !!}</td></tr>
        <tr><td class="title">Aktor</td><td class="content">{{ $singleUseCase->aktor }}</td></tr>
        <tr><td class="title">Tujuan</td><td class="content">{!! $singleUseCase->tujuan !!}</td></tr>
        <tr><td class="title">Kondisi Awal</td><td class="content">{!! $singleUseCase->kondisi_awal !!}</td></tr>
        <tr><td class="title">Kondisi Akhir</td><td class="content">{!! $singleUseCase->kondisi_akhir !!}</td></tr>
        <tr><td class="title">Aksi Aktor</td><td class="content">{!! $singleUseCase->aksi_aktor !!}</td></tr>
        <tr><td class="title">Reaksi Sistem</td><td class="content">{!! $singleUseCase->reaksi_sistem !!}</td></tr>
    </table>
</body>
</html>