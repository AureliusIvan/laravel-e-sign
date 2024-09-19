<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanAkhirForm extends Model
{
    use HasFactory;

    protected $table = 'laporan_akhir_form';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'judul_form',
        'keterangan',
        'dibuka',
        'ditutup',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (LaporanAkhirForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function laporanAkhir(): HasMany
    {
        return $this->hasMany(LaporanAkhir::class);
    }
}
