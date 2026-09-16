<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $laporan = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($startDate, fn ($query, $startDate) => $query->whereDate('tgl_pinjam', '>=', $startDate))
            ->when($endDate, fn ($query, $endDate) => $query->whereDate('tgl_pinjam', '<=', $endDate))
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest('tgl_pinjam')
            ->get();

        $dicetakOleh = auth()->user()->name ?? 'Petugas';

        $pdf = Pdf::loadView('laporan.pdf', compact('laporan', 'startDate', 'endDate', 'status', 'dicetakOleh'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman.pdf');
    }
}