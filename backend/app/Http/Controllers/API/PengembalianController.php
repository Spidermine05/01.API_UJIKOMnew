<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Http\Requests\Pengembalian\UpdatePengembalianRequest;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class PengembalianController extends Controller
{
   
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat','petugas']);
        if ($user->role === 'peminjam') {
            $query->whereHas('peminjaman', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $pengembalian = $query->latest()->get();
        return response()->json([
            'message' => 'Riwayat pengembalian berhasil diambil',
            'data' => $pengembalian
        ]);
    }

    
    public function store(StorePengembalianRequest $request): JsonResponse
    {
        try {
            $pengembalian = DB::transaction(function () use ($request) {
                //Kunci baris peminjaman ini selama transaksi agar tidak dimanipulasi proses lain
                $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->findOrFail($request->peminjaman_id);

                //guarding: pastikan statusnya dipinjam
                if ($peminjaman->status !== 'dipinjam') {
                    throw new Exception("Data ditolak. Peminjaman ini berstatus '{$peminjaman->status}', bukan 'dipinjam'.");
                }
                //cek keterlambatan menggunakan carbon
                $tglKembaliPlan = Carbon::parse($peminjaman->tgl_kembali_plan)->startOfDay();
                    $hariIni = Carbon::now()->startOfDay();
                    //jika hari ini lebih besar dari tanggal rencana kembali, maka telat
                    $statusPeminjamanBaru = $hariIni->greaterThan($tglKembaliPlan) ? 'telat' : 'dikembalikan';

                    // 1. Inserrt data ketabel pengembalian
                    $pengembalian = Pengembalian::create([
                        'peminjaman_id' => $peminjaman->id,
                        'tgl_kembali' => now()->toDateString(),
                        'kondisi_kembali' => $request->kondisi_kembali,
                        'denda' => $request->denda ?? 0, //deafult 0 jika null
                        'petugas_id' => auth()->id(), //ambil id user petugas yang sedang login
                    ]);
                    //2. ubah status di tabel peminjaman utama
                    $peminjaman->update(['status' => $statusPeminjamanBaru]);

                    //3. kembalikkan (tambah) stok alat berdasarkan detail_pinjam
                    foreach ($peminjaman->detailPinjam as $detail) {
                        $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                        //increment() otomatis menambah nilai pada field yang ditentukan

                        $alat->increment('stok', $detail->jumlah);
                    }

                    //opsional: Catat ke log aktivitas petugas
                    /** @var User $petugas */
                    $petugas = auth()->user();
                    $petugas->logAktivitas()->create([
                        'aktivitas' => "Memproses pengembalian peminjaman ID: #{$peminjaman->id} dengan status akhir: {$statusPeminjamanBaru}."
                    ]);
                    //Load relasi agar response JSON lebih informatif
                    return $pengembalian->load(['peminjaman.user', 'petugas']);
            });
            return response()->json([
                'message' => 'Proses pengembalian alat berhasil diselesaikan',
                'data' => $pengembalian
            ],201);
        } catch (Exception $e) {
            //tangkap pesan error dari throw exception diatas (misal status bukan dipinjam)
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    
    public function show(Pengembalian $pengembalian): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        //eager load Relasi
        $pengembalian->load(['peminjaman.user','peminjaman.detailPinjam.alat','petugas']);

        //otorasi privasi
        if ($user->role === 'peminjam' && $pengembalian->peminjaman->user_id !== $user->id){
            return response()->json(['message' => 'Akses ditolak '],403);
        }
        return response()->json([
            'message' => 'Detail pengembalian berhasil diambil',
            'data' => $pengembalian
        ]);
        
    }

    public function update(UpdatePengembalianRequest $request, Pengembalian $pengembalian): JsonResponse
    {
        //Mengamankan data dengan memebatasi field yang diboleh dikoreksi petugas
        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda ?? $pengembalian->denda,
        ]);
        return response()->json([
            'message' => 'Data Pengembalian berhasil diperbarui',
            'data' => $pengembalian->load(['peminjaman.user','petugas'])
        ]);
    }

    
    public function destroy(Pengembalian $pengembalian): JsonResponse
    {
        try {
            DB::transaction(function () use ($pengembalian) {
                $peminjaman = Peminjaman::with('detailPinjam')->lockForUpdate()->findOrFail($pengembalian->peminjaman_id);

                // Tarik kembali stok ke gudang (karena status kembali dibatalkan, stok berkurang lagi)
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::lockForUpdate()->findOrFail($detail->alat_id);
                    

                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception("Gagal membatalkan pengembalian. stok alat '{$alat->nama_alat}' Saat ini tidak mencukupi untuk ditarik kembali");
                    }
                    $alat->decrement('stok', $detail->jumlah);
                }
                //kembalikan status peminjaman master menjadi dipinjam kembali
                $peminjaman->update(['status' => 'dipinjam']);

                //log aktivitas jika metode/relasi tersedia
                /** @var User $petugas */
                $petugas = auth()->user();
                $petugas->logAktivitas()?->create(['aktivitas' => "Membatalkan pengembalian ID: #{$pengembalian->id}"]);

                $pengembalian->delete();
            });
            return response()->json([
                'message' => 'Data Pengembalian berhasil dihapus. stok dan status peminjaman telah dikembalikan ke kondisi semula'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}