<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetilPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat yang tersedia
    public function katalogAlat() {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog', compact('alats'));
    }
    public function ajukanPeminjaman(Request $request) {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Buat header peminjam
            $peminjam = Peminjaman::create ([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);
            
            // Masukkan daftar alat yang dipinjam ke detail_pinjam
            foreach ($request->alat_id as $index => $alatId) {
                DetilPinjam::create([
                    'peminjaman_id' => $peminjam->id,
                    'alat_id' => $alatId,
                    'jumlah' => $request->jumlah[$index],
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success','Pengajuan peminjaman berhasil dikirim.');
        } catch(\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error','Gagal mengajukan peminjaman: '. $e->getMessage());
        }
    }
    public function riwayatPeminjaman(){
        $peminjamans = Peminjaman::with(['detailPinjam.alat.kategori', 'pengembalian'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

            return view('peminjam.riwayat', compact('peminjamans'));
    }

    // Peminjam melaporkan bahwa alat sudah/akan dikembalikan (menunggu verifikasi petugas)
    public function ajukanPengembalian($id) {
        $peminjaman = Peminjaman::where('user_id', auth()->id())->findOrFail($id);

        if ($peminjaman->status !== 'dipinjam') {
            return redirect()->back()->with('error', 'Pengajuan pengembalian hanya bisa dilakukan untuk alat yang sedang dipinjam.');
        }

        if ($peminjaman->tgl_pengajuan_kembali) {
            return redirect()->back()->with('error', 'Anda sudah mengajukan pengembalian untuk peminjaman ini, tinggal menunggu verifikasi petugas.');
        }

        $peminjaman->update(['tgl_pengajuan_kembali' => now()]);

        return redirect()->back()->with('success', 'Pengembalian berhasil diajukan. Silakan serahkan alat ke petugas untuk diverifikasi.');
    }

    // Membatalkan pengajuan pengembalian (misal salah klik)
    public function batalkanPengajuanKembali($id) {
        $peminjaman = Peminjaman::where('user_id', auth()->id())->findOrFail($id);

        if ($peminjaman->status !== 'dipinjam' || !$peminjaman->tgl_pengajuan_kembali) {
            return redirect()->back()->with('error', 'Tidak ada pengajuan pengembalian yang bisa dibatalkan.');
        }

        $peminjaman->update(['tgl_pengajuan_kembali' => null]);

        return redirect()->back()->with('success', 'Pengajuan pengembalian dibatalkan.');
    }
}