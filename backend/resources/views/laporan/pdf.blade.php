<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h3>{{ $title }}</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Tgl Pinjam</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporan as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ $item->tgl_pinjam }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>