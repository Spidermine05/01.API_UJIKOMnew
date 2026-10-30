<?php

namespace Database\Seeders;

use App\Models\LogAktivitas;
use Illuminate\Database\Seeder;

class LogAktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logs = [
            ['user_id' => 1, 'altivitas' => 'Melakukan Import data master alat baru sebanyak 5 entitas.'],
            ['user_id' => 2, 'altivitas' => 'Menyetujui permohonan peminjaman ID #4.'],
            ['user_id' => 3, 'altivitas' => 'Mengajukan peminjaman baru untuk kebutuuhan praktik kelompok.'],
            ['user_id' => 2, 'altivitas' => 'Memproses Pengembalian alat telat untuk peminjaman ID #3 dan mengenakan denda.'],
            ['user_id' => 1, 'altivitas' => 'Mengubah konfigurasi hak akses aplikasi.'],

        ];

        foreach ($logs as $log);
    }
}
