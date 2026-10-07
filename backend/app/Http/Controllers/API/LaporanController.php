<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 1. Validasi Input Parameter
        $validator = Validator::make($request->all(), [
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date'   => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status'     => ['nullable', 'string', 'in:diajukan,dipinjam,dikembalikan,telat,selesai'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parameter filter tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Eager loading untuk mencegah masalah N+1 Query
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian.petugas']);

        // Filter: Rentang Tanggal Pinjam
        $query->when($request->filled('start_date'), fn ($q) => $q->whereDate('tgl_pinjam', '>=', $request->start_date));
        $query->when($request->filled('end_date'), fn ($q) => $q->whereDate('tgl_pinjam', '<=', $request->end_date));

        // Filter: Status Peminjaman
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // (Pagination)
        $perPage = $request->input('per_page', 15);
        $laporan = $query->latest()->paginate($perPage);

        //API Resource khusus untuk instance Paginate
        return PeminjamanResource::collection($laporan)
            ->additional([
                'message' => 'Laporan peminjaman berhasil ditarik.'
            ])
            ->response();
    }
        public function export(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date'   => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status'     => ['nullable', 'string', 'in:diajukan,dipinjam,dikembalikan,telat,selesai'],
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $laporan = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($startDate, fn ($q, $v) => $q->whereDate('tgl_pinjam', '>=', $v))
            ->when($endDate, fn ($q, $v) => $q->whereDate('tgl_pinjam', '<=', $v))
            ->when($status, fn ($q, $v) => $q->where('status', $v))
            ->latest('tgl_pinjam')
            ->get();

        $dicetakOleh = auth()->user()->name;

        return Pdf::loadView('laporan.pdf', compact('laporan', 'startDate', 'endDate', 'status', 'dicetakOleh'))
            ->setPaper('a4', 'landscape')
            ->download('laporan-peminjaman.pdf');
    }
}