<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date'   => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status'     => ['nullable', 'string', 'in:diajukan,dipinjam,dikembangkan,telat'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parameter filter tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian.petugas']);

        $query->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
            $q->whereBetween('tgl_pinjam', [$request->start_date, $request->end_date]);
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $laporan = $query->latest()->get();

        $pdf = Pdf::loadView('laporan.pdf', [
            'title'   => 'Laporan Peminjaman',
            'laporan' => $laporan,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman.pdf');
    }
}