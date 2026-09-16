<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
// Menampilkan daftar pengajuan peminjaman dari siswa/pengembalian
    public function indexPeminjaman(Request $request) {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user','detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }
    // Menyetujui peminjaman ( Mengubah status & mengurangi stok alat)
    public function setujuPeminjaman($id) {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            //Kurangi stok alat secara otomatis
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success','Peminjaman disetujui dan stok alat dikurangi.');  
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error','Terjadi Kesalahan: ' . $e->getMessage());
        }
    }
    // Menampilkan daftar barang yang sedang dipinjam & riwayat pengembalian (khusus petugas)
    public function indexPengembalian(Request $request) {
        $search = $request->input('search');

        $dipinjam = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'dipinjam')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            // Yang sudah diajukan pengembaliannya oleh peminjam ditampilkan paling atas
            ->orderByRaw('tgl_pengajuan_kembali IS NULL')
            ->orderByDesc('tgl_pengajuan_kembali')
            ->orderBy('tgl_kembali_plan')
            ->get();

        $riwayat = Pengembalian::with(['peminjaman.user', 'peminjaman.detailPinjam.alat', 'petugas'])
            ->latest('created_at')
            ->take(20)
            ->get();

        return view('petugas.pengembalian.index', compact('dipinjam', 'riwayat', 'search'));
    }
    public function prosesPengembalian(Request $request, $PeminjamanId) {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($PeminjamanId);

            //Simpan data pengembalian
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            //Update status peminjaman jadi selesai
            $peminjaman->update(['status' => 'selesai']);

            //Kembalikan stok alat ke inventaris
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success','Pengembalian berhasil dicatat dan stok di pulihkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error','Terjadi kesalahan: '. $e->getMessage());
        }
    }
    // tolak peminjaman
    public function tolakPeminjaman($id)
    {
    try {
        $peminjaman = Peminjaman::findOrFail($id);

        // pastikan status masih diajukan
        if ($peminjaman->status == 'diajukan') {
            $peminjaman->delete();
            return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak');
        }

        return redirect()->back()->with('error', 'Status peminjaman sudah berubah');
    } catch (\Exception $e) {
    return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
 
        }
    }

    // Laporan Peminjaman (bisa difilter tanggal & status, dan dicetak)
    public function indexLaporan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status');

        $laporan = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('tgl_pinjam', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('tgl_pinjam', '<=', $endDate);
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest('tgl_pinjam')
            ->get();

        return view('petugas.laporan.index', compact('laporan', 'startDate', 'endDate', 'status'));
    }
}