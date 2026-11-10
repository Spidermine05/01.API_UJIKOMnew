<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanDitolakNotification extends Notification
{
    use Queueable;

    protected $alasan;

    public function __construct($alasan = null)
    {
        $this->alasan = $alasan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => 'Pengajuan Peminjaman Ditolak',
            'pesan' => 'Mohon maaf, pengajuan peminjaman alat Anda tidak disetujui oleh petugas.',
            'alasan' => $this->alasan ?? 'Tidak ada alasan yang diberikan.',
            'tipe' => 'peminjaman_ditolak',
            'tanggal' => now()->toDateTimeString(),
        ];
    }
}
