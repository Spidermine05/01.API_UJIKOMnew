<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id','tgl_pinjam','tgl_kembali_plan','status','tgl_pengajuan_kembali'
    ];
    protected function casts(): array {
        return[
            'tgl_pinjam' => 'date:Y-m-d',
            'tgl_kembali_plan' => 'date:Y-m-d',
            'tgl_pengajuan_kembali' => 'datetime',
        ];
    }
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    public function detailPinjam(): HasMany {
        return $this->hasMany(DetilPinjam::class);
    }
    public function pengembalian(): HasOne {
        return $this->hasOne(Pengembalian::class);
    }
}