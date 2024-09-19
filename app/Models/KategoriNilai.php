<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriNilai extends Model
{
    use HasFactory;

    protected $table = 'kategori_nilai';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'kategori',
        'persentase',
        'user',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (KategoriNilai $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function kategoriNilaiDetail(): HasMany
    {
        return $this->hasMany(KategoriNilaiDetail::class);
    }
}
