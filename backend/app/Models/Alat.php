<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    protected $table = 'alat';

    protected $fillable = [
        'kategori_id','nama_alat','stok','status_kondisi','deskripsi','gambar'
    ];
    protected function casts(): array{
        return [
            'stok' => 'integer',
        ];
    }
    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class);
    }
    public function detailPinjam(): HasMany{
        return $this->hasMany(DetilPinjam::class);
    }
        public function getGambarUrlAttribute(): ?string
    {
        if (blank($this->gambar)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $this->gambar), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // data lama/seeder yang cuma nama file
        if (!str_contains($path, '/')) {
            $path = 'storage/alat/' . $path;
        }

        return asset($path);
    }
}
