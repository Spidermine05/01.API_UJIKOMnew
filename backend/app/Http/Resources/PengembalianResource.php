<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengembalianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'peminjaman_id' => $this->peminjaman_id,
            'peminjam' => $this->whenLoaded('peminjaman', fn () => $this->peminjaman?->user?->name),
            'item_dipinjam' => $this->when(
                $this->relationLoaded('peminjaman') && $this->peminjaman?->relationLoaded('detailPinjam'),
                fn () => $this->peminjaman->detailPinjam->map(fn ($d) => [
                    'nama_alat' => $d->alat?->nama_alat ?? 'Alat Dihapus/Tidak ditemukan',
                    'jumlah' => (int) $d->jumlah,
                ])
            ),
            'tgl_kembali' => $this->tgl_kembali?->format('Y-m-d'),
            'kondisi_kembali' => $this->kondisi_kembali,
            'denda' => (int) $this->denda,
            'petugas_penerima' => $this->whenLoaded('petugas', fn () => $this->petugas?->name ?? 'Sistem'),
        ];
    }
}