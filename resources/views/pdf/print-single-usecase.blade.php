<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Detail Usecase</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
        .title { font-weight: bold; background-color: #f2f2f2; }
    </style>
</head>
<body onload="window.print()">
    <h2>Detail Usecase</h2>
    <table>
        <tr><td class="title">ID Usecase</td><td>UC - {{ $singleUseCase->id }}</td></tr>
        <tr><td class="title">Nama Proses</td><td>{{ $singleUseCase->nama_proses }}</td></tr>
        <tr><td class="title">Deskripsi Aksi</td><td>{!! $singleUseCase->deskripsi_aksi !!}</td></tr>
        <tr><td class="title">Aktor</td><td>{{ $singleUseCase->aktor }}</td></tr>
        <tr><td class="title">Tujuan</td><td>{!! $singleUseCase->tujuan !!}</td></tr>
        <tr><td class="title">Kondisi Awal</td><td>{!! $singleUseCase->kondisi_awal !!}</td></tr>
        <tr><td class="title">Kondisi Akhir</td><td>{!! $singleUseCase->kondisi_akhir !!}</td></tr>
        <tr><td class="title">Aksi Aktor</td><td>{!! $singleUseCase->aksi_aktor !!}</td></tr>
        <tr><td class="title">Reaksi Sistem</td><td>{!! $singleUseCase->reaksi_sistem !!}</td></tr>
    </table>
</body>
</html>
