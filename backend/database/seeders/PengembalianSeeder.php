<?php

namespace Database\Seeders;

use App\Models\Pengembalian;
use Illuminate\Database\Seeder;

class PengembalianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pengembalian = [
            [
                'peminjaman_id' => 1,
                'tgl_kembali' => '2026-06-04',
                'kondisi_kembali' => 'Lengkap dan berfungsi Baik',
                'denda' => 0,
                'petugas_id' => 2, //Sekulic (Petugas)
            ],
            [
                'peminjaman_id' => 2,
                'tgl_kembali' => '2026-06-05',
                'kondisi_kembali' => 'Lengkap dan berfungsi Baik',
                'denda' => 0,
                'petugas_id' => 2, //Sekulic (Petugas)
            ],
            [
                'peminjaman_id' => 3,
                'tgl_kembali' => '2026-06-09', //telat 3 hari dari tanggal 6
                'kondisi_kembali' => 'Lengkap, kasing sedikit tergores',
                'denda' => 30000, //asumsi denda perhari 10k
                'petugas_id' => 2, //Sekulic (Petugas)

            ],

        ];

        foreach ($pengembalian as $kembali) {
            Pengembalian::create($kembali);
        }
    }
}
