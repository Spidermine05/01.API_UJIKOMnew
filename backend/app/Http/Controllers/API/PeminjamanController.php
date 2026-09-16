<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjaman\StorePeminjamanRequest;
use App\Http\Resources\PeminjamanResource;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Exception;

class PeminjamanController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $query = Peminjaman::with(['user','detailPinjam.alat','pengembalian']);

        if ($user->role === 'peminjam') {
            $query->where('user_id', $user->id);
        }
        $peminjaman = $query->latest()->get();

        return response()->json([
            'message' => 'Daftar Peminjam berhasil diambil.',
            'data' => PeminjamanResource::collection($peminjaman)
        ]);
    }

    public function store(StorePeminjamanRequest $request): JsonResponse
    {
        try {
            $peminjaman = DB::transaction(function () use ($request) {
                $user = auth()->user();
                $peminjaman = Peminjaman::create([
                    'user_id' => $user->id,
                    'tgl_pinjam' => now()->toDateString(),
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                    'status' => 'diajukan',
                ]);
                foreach ($request->items as $item) {
                    //lockForUpdate mengunci baris data di database sampaai transaksi ini COMMIT
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);
                        if ($alat->stok < $items['jumlah']) {
                            throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}");
                        }
                        DetailPinjam::create([
                            'peminjaman_id' => $peminjaman_id,
                            'alat_id' => $item['alat_id'],
                            'jumlah' => $item['jumlah'],
                        ]);
                }
                return $peminjaman->load(['user','detailPinjam,alat']);
            });

            return response()->json([
                'message' => 'Pemin jaman berhasil diajukan. menunggu persetujuan petugas',
                'data' => new PeminjamanResource($peminjaman) //dioptimalkan menggunakan resource
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function show(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'message' => 'Detail pemminjam berhasil diambil',
            'data' => new PeminjamanResource($peminjaman->load(['user','detailPinjam.alat','pengembalian']))
        ]);
    }

    public function update(storePeminjamanRequest $request, Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        if ($peminjaman->status !== 'diajukan') {
            return response()->json([
                'message' => "Peminjaman tidak dapat diubah karena status saat ini: {$peminjaman->status}."
            ], 400);
        }
        try {
            DB::transaction(function () use ($request, $peminjaman) {
                $peminjaman->update([
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                ]);
                $peminjaman->detailPinjam()->delete();

                foreach ($request as $item) {
                    //ditambahkan lockforupdate agar konsisten amana dari race condicition saat update data draft
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);

                    if ($alat->stok < $item['jumlah']) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' Tidak mencukupi.");
                    }
                    DetailPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['alat_id'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
            });

            return response()->json([
                'message' => 'Data permohonan peminjaman berhasil diperbarui.',
                'data' => new PeminjamanResource($peminjaman->load(['user','detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($peminjaman->status !== 'diajukan') {
            return response()->json(['message' => 'Peminjaman tidak dapat dibatalakan.'], 400);
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjam()->delete(); //hapus child record terlebih dahulu
            $peminjaman->delete();
        });
        return response()->json([
            'message' => 'Permohonan peminjaman berhasil dibatalkan dan dihapus.'
        ]);
    }
    public function approve(Peminjaman $peminjaman): JsonResponse
{
    if ($peminjaman->status !== 'diajukan') {
        return response()->json([
            'message' => "Persetujuan gagal. Status saat ini: {$peminjaman->status}."
        ], 400);
    }

    try {
        DB::transaction(function () use ($peminjaman) {

            $peminjaman->update([
                'status' => 'dipinjam'
            ]);

            foreach ($peminjaman->detailPinjam as $detail) {

                // Mengunci baris alat sebelum stok dikurangi
                $alat = Alat::lockForUpdate()
                    ->findOrFail($detail->alat_id);

                if ($alat->stok < $detail->jumlah) {
                    throw new Exception(
                        "Persetujuan gagal. Stok alat '{$alat->nama_alat}' tidak mencukupi."
                    );
                }

                $alat->decrement('stok', $detail->jumlah);
            }
        });

            return response()->json([
                'message' => 'Peminjaman disetujui stok alat telah otomatis dikurangi.',
                'data' => new PeminjamanResource($peminjaman->load(['user','detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function riwayat(): JsonResponse
    {
        $riwayat = Peminjaman::with(['detailPinjam.alat','pengembalian'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat peminjaman anda',
            'data' => PeminjamanResource::collection($riwayat)
        ]);
    }
}