<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Usecase - {{ $menu->menu_nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Use Case - {{ $menu->menu_nama }}</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Proses</th>
                <th>Aktor</th>
                <th>Tujuan</th>
                <th>Kondisi Awal</th>
                <th>Kondisi Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($useCases as $uc)
                <tr>
                    <td>{{ $uc->nama_proses }}</td>
                    <td>{{ $uc->aktor }}</td>
                    <td>{{ $uc->tujuan }}</td>
                    <td>{{ $uc->kondisi_awal }}</td>
                    <td>{{ $uc->kondisi_akhir }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>