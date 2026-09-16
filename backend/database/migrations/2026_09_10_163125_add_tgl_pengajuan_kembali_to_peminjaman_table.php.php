<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan penanda waktu ketika peminjam mengajukan pengembalian alat.
     * Status peminjaman TIDAK ikut berubah di sini — tetap "dipinjam" sampai
     * petugas benar-benar memverifikasi & memproses pengembaliannya.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->timestamp('tgl_pengajuan_kembali')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn('tgl_pengajuan_kembali');
        });
    }
};