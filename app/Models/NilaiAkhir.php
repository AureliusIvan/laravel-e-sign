<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NilaiAkhir extends Model
{
    use HasFactory;

    protected $table = 'nilai_akhir';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'jadwal_sidang_id',
        'mahasiswa_id',
        'pembimbing1',
        'nilai_pembimbing1',
        'pembimbing2',
        'nilai_pembimbing2',
        'total_nilai_pembimbing',
        'penguji',
        'nilai_penguji',
        'ketua_sidang',
        'nilai_ketua_sidang',
        'nilai_akhir',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (NilaiAkhir $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
