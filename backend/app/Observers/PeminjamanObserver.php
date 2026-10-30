<?php

namespace App\Observers;

use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class PeminjamanObserver
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
    public function created(Peminjaman $peminjaman): void
    {
        $namaPeminjam = $peminjaman->user?->name ?? 'User';

        if (Auth::check() && Auth::id() == $peminjaman->user_id) {
            $this->catatLog("Peminjam ({$namaPeminjam}) mengajukan permohonan peminjaman baru (ID: #{$peminjaman->id})");
        } else {
            $namaPetugas = Auth::user()?->name ?? 'Petugas';
            $this->catatLog("{$namaPetugas} mencatat permohonan peminjaman baru atas nama {$namaPeminjam} (ID: #{$peminjaman->id})");
        }
    }

    
    public function updated(Peminjaman $peminjaman): void
    {
        if ($peminjaman->wasChanged('status')) {
            $this->catatLog("Status peminjaman (ID: #{$peminjaman->id}) berubah menjadi: '{$peminjaman->status}'");
        } else {
            if (!empty($peminjaman->getChanges())) {
                $this->catatLog("Memperbarui detail data peminjam (ID: #{$peminjaman->id})");
            }
        }
    }

    
    public function deleted(Peminjaman $peminjaman): void
    {
        $this->catatLog("Membatalkan/Menghapus permohonan peminjaman (ID: #{$peminjaman->id})");
    }

    
    public function restored(Peminjaman $peminjaman): void
    {
        //
    }

    
    public function forceDeleted(Peminjaman $peminjaman): void
    {
        //
    }
}