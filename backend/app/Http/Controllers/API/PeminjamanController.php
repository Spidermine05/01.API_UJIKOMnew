<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjaman\StorePeminjamanRequest;
use App\Http\Resources\PeminjamanResource;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetilPinjam;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Exception;

class PeminjamanController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $query = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian']);

        if ($user->role === 'peminjam') {
            $query->where('user_id', $user->id);
        }
        $peminjaman = $query->latest()->paginate(15);

        return PeminjamanResource::collection($peminjaman)
            ->additional(['message' => 'Daftar Peminjam berhasil diambil.'])
            ->response();
    }

    public function store(StorePeminjamanRequest $request): JsonResponse
    {
        if (auth()->user()->role === 'petugas') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        try {
            $peminjaman = DB::transaction(function () use ($request) {
                $user = auth()->user();
                $userId = $user->role === 'admin' ? ($request->user_id ?? $user->id) : $user->id;
                $peminjaman = Peminjaman::create([
                    'user_id'=> $userId,
                    'tgl_pinjam' => now()->toDateString(),
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                    'status' => 'diajukan',
                ]);

                foreach ($request->items as $item) {
                    // lockForUpdate mengunci baris data di database sampai transaksi ini COMMIT
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);

                    if ($alat->stok < $item['jumlah']) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}");
                    }

                    DetilPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['alat_id'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }

                return $peminjaman->load(['user', 'detailPinjam.alat']);
            });

            return response()->json([
                'message' => 'Peminjaman berhasil diajukan. Menunggu persetujuan petugas.',
                'data' => new PeminjamanResource($peminjaman)
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'peminjam' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'message' => 'Detail peminjaman berhasil diambil.',
            'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat', 'pengembalian']))
        ]);
    }

    public function update(StorePeminjamanRequest $request, Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($peminjaman->status !== 'diajukan') {
            return response()->json([
                'message' => "Peminjaman tidak dapat diubah karena status saat ini: {$peminjaman->status}."
            ], 409);
        }

        try {
            DB::transaction(function () use ($request, $peminjaman) {
                $peminjaman->update([
                    'tgl_kembali_plan' => $request->tgl_kembali_plan,
                ]);
                $peminjaman->detailPinjam()->delete();

                foreach ($request->items as $item) {
                    // lockForUpdate agar aman dari race condition saat update data pengajuan
                    $alat = Alat::lockForUpdate()->findOrFail($item['alat_id']);

                    if ($alat->stok < $item['jumlah']) {
                        throw new Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}");
                    }

                    DetilPinjam::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['alat_id'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
            });

            return response()->json([
                'message' => 'Data permohonan peminjaman berhasil diperbarui.',
                'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && $peminjaman->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($peminjaman->status !== 'diajukan') {
            return response()->json(['message' => 'Peminjaman tidak dapat dibatalkan.'], 409);
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPinjam()->delete(); // hapus child record terlebih dahulu
            $peminjaman->delete();
        });

        return response()->json([
            'message' => 'Permohonan peminjaman berhasil dibatalkan dan dihapus.'
        ]);
    }

    public function approve(Peminjaman $peminjaman): JsonResponse
    {
        try {
            $peminjaman = DB::transaction(function () use ($peminjaman) {

                // Kunci baris peminjaman dulu, baru cek status (anti double-approve / race condition)
                $peminjaman = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

                if ($peminjaman->status !== 'diajukan') {
                    throw new Exception("Persetujuan gagal. Status saat ini: {$peminjaman->status}.", 409);
                }

                $peminjaman->update(['status' => 'dipinjam']);

                foreach ($peminjaman->detailPinjam as $detail) {
                    // Mengunci baris alat sebelum stok dikurangi
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);

                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception(
                            "Persetujuan gagal. Stok alat '{$alat->nama_alat}' tidak mencukupi."
                        );
                    }

                    $alat->decrement('stok', $detail->jumlah);
                }

                return $peminjaman;
            });

            return response()->json([
                'message' => 'Peminjaman disetujui, stok alat telah otomatis dikurangi.',
                'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat']))
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() === 409 ? 409 : 422);
        }
    }

        public function reject(Peminjaman $peminjaman): JsonResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjaman = Peminjaman::lockForUpdate()->findOrFail($peminjaman->id);

                if ($peminjaman->status !== 'diajukan') {
                    throw new Exception("Penolakan gagal. Status saat ini: {$peminjaman->status}.", 409);
                }

                $peminjaman->detailPinjam()->delete();
                $peminjaman->delete();
            });

            return response()->json(['message' => 'Pengajuan peminjaman berhasil ditolak.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
        public function ajukanKembali(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($peminjaman->status !== 'dipinjam') {
            return response()->json(['message' => 'Pengajuan pengembalian hanya bisa dilakukan untuk alat yang sedang dipinjam.'], 409);
        }
        if ($peminjaman->tgl_pengajuan_kembali) {
            return response()->json(['message' => 'Pengembalian sudah diajukan, menunggu verifikasi petugas.'], 409);
        }

        $peminjaman->update(['tgl_pengajuan_kembali' => now()]);

        return response()->json([
            'message' => 'Pengembalian berhasil diajukan. Silakan serahkan alat ke petugas untuk diverifikasi.',
            'data' => new PeminjamanResource($peminjaman->load(['user', 'detailPinjam.alat'])),
        ]);
    }

    public function batalAjukanKembali(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->user_id !== auth()->id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        if ($peminjaman->status !== 'dipinjam' || !$peminjaman->tgl_pengajuan_kembali) {
            return response()->json(['message' => 'Tidak ada pengajuan pengembalian yang bisa dibatalkan.'], 409);
        }

        $peminjaman->update(['tgl_pengajuan_kembali' => null]);

        return response()->json(['message' => 'Pengajuan pengembalian dibatalkan.']);
    }

    public function riwayat(): JsonResponse
    {
        $riwayat = Peminjaman::with(['detailPinjam.alat', 'pengembalian'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat peminjaman anda',
            'data' => PeminjamanResource::collection($riwayat)
        ]);
    }
}