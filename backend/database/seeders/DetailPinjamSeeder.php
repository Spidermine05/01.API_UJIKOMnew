<?php

namespace Database\Seeders;

use App\Models\DetilPinjam;
use Illuminate\Database\Seeder;

class DetailPinjamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $details =[
            ['peminjaman_id' => 1, 'alat_id' => 1, 'jumlah' => 2], //peminjam 2 mikrotik
            ['peminjaman_id' => 2, 'alat_id' => 2, 'jumlah' => 1], //peminjam 1 kamera
            ['peminjaman_id' => 3, 'alat_id' => 3, 'jumlah' => 1], //peminjam 1 mini pc
            ['peminjaman_id' => 4, 'alat_id' => 4, 'jumlah' => 2], //peminjam 2 tang crimping
            ['peminjaman_id' => 5, 'alat_id' => 5, 'jumlah' => 3], //peminjam 3 adapter

        ];

        foreach ($details as $detail) {
            DetilPinjam::create($detail);
        }
    }
}
