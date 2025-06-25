<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NilaiSidang extends Model
{
    use HasFactory;

    protected $table = 'nilai_sidang';

    protected $fillable = [
        'uuid',
        'mahasiswa_id',
        'dosen_id',
        'nilai_akhir_id',
        'kategori_nilai_detail_id',
        'nilai',
        'is_editable',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (NilaiSidang $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
