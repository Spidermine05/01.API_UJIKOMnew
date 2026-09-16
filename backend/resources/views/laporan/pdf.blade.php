<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 90px 50px 70px 50px;
        }
        body {
            font-family: 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #2b2b2b;
        }
        .kop {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .kop .instansi {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #1f2937;
            text-transform: uppercase;
        }
        .kop .sub {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }
        .judul {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .judul-sub {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-bottom: 16px;
        }
        table.meta {
            width: 100%;
            font-size: 10px;
            margin-bottom: 14px;
        }
        table.meta td {
            padding: 2px 0;
            vertical-align: top;
        }
        table.meta td.label {
            width: 110px;
            color: #555;
        }
        table.meta td.sep {
            width: 12px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.data thead th {
            background-color: #1f2937;
            color: #ffffff;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 6px;
            text-align: left;
            border: 1px solid #1f2937;
        }
        table.data tbody td {
            padding: 7px 6px;
            border: 1px solid #d1d5db;
            font-size: 10px;
            vertical-align: top;
        }
        table.data tbody tr:nth-child(even) {
            background-color: #f3f4f6;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border: 1px solid #9ca3af;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #374151;
        }
        .muted {
            color: #6b7280;
            font-size: 9px;
        }
        .footer-note {
            margin-top: 18px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }

        .ttd-wrap {
            width: 100%;
            margin-top: 46px;
        }
        .ttd-box {
            width: 220px;
            float: right;
            text-align: center;
            font-size: 10px;
        }
        .ttd-space {
            height: 55px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="instansi">MariPinjams &mdash; Sistem Manajemen Peminjaman</div>
        <div class="sub">Laporan Resmi Aktivitas Peminjaman Alat</div>
    </div>

    <div class="judul">Laporan Peminjaman</div>
    <div class="judul-sub">
        @if($startDate && $endDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}
            &ndash; {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
        @else
            Seluruh Periode
        @endif
    </div>

    <table class="meta">
        <tr>
            <td class="label">Dicetak oleh</td>
            <td class="sep">:</td>
            <td>{{ $dicetakOleh }}</td>
            <td class="label" style="width:90px;">Tanggal cetak</td>
            <td class="sep">:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Total data</td>
            <td class="sep">:</td>
            <td>{{ $laporan->count() }} transaksi</td>
            <td class="label">Status filter</td>
            <td class="sep">:</td>
            <td>{{ $status ? ucfirst($status) : 'Semua status' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:11%">Peminjam</th>
                <th style="width:23%">Detail Alat</th>
                <th style="width:12%">Tgl Pinjam</th>
                <th style="width:13%">Rencana Kembali</th>
                <th style="width:9%">Status</th>
                <th style="width:20%">Pengembalian</th>
                <th style="width:12%">Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
                <tr>
                    <td>{{ $item->user->name ?? 'User Dihapus' }}</td>
                    <td>
                        @foreach($item->detailPinjam as $detail)
                            {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }})<br>
                        @endforeach
                    </td>
                    <td>{{ $item->tgl_pinjam }}</td>
                    <td>{{ $item->tgl_kembali_plan }}</td>
                    <td><span class="badge">{{ ucfirst($item->status) }}</span></td>
                    <td>
                        {{ $item->pengembalian->tgl_kembali ?? '-' }}
                        @if($item->pengembalian)
                            <br><span class="muted">{{ $item->pengembalian->kondisi_kembali }}</span>
                        @endif
                    </td>
                    <td>{{ ($item->pengembalian->denda ?? 0) > 0 ? 'Rp ' . number_format($item->pengembalian->denda, 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center">Tidak ada data laporan untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh sistem MariPinjams dan sah tanpa memerlukan tanda tangan basah, kecuali dinyatakan lain.
    </div>

    <div class="ttd-wrap">
        <div class="ttd-box">
            <div>Mengetahui,</div>
            <div>Petugas Peminjaman</div>
            <div class="ttd-space"></div>
            <div class="ttd-name">( {{ $dicetakOleh }} )</div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>