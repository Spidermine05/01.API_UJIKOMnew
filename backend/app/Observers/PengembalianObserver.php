<?php

namespace App\Observers;

use App\Models\Pengembalian;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class PengembalianObserver
{
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }
    public function created(Pengembalian $pengembalian): void
    {
        $this->catatLog("Memproses pengembalian alat untuk peminjaman ID: #{$pengembalian->peminjaman_id}");
    }

    
    public function updated(Pengembalian $pengembalian): void
    {
        $perubahan = array_diff(array_keys($pengembalian->getChanges()),
        ['update_at']);
        
            if (!empty($perubahan)) {
                $kolom = implode(', ', $perubahan);
                $this->catatLog("Merevisi data pengembalian (ID kembali: #{$pengembalian->id}, Peminjaman ID: #{$pengembalian->peminjaman_id}, Kolom diubah: {$kolom})");
            }
    }

    
    public function deleted(Pengembalian $pengembalian): void
    {
        $this->catatLog("Membatalkan/Menghapus riwayat pengembalian (Peminjaman ID: #{$pengembalian->peminjaman_id})");
    }

    
    public function restored(Pengembalian $pengembalian): void
    {
        //
    }

   
    public function forceDeleted(Pengembalian $pengembalian): void
    {
        //
    }
}
