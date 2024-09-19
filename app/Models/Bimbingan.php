<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bimbingan extends Model
{
    use HasFactory;

    protected $table = 'bimbingan';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'mahasiswa_id',
        'dosen_id',
        'tanggal_bimbingan',
        'isi_bimbingan',
        'saran',
        'status',
        'note',
        'available_at_tahun',
        'available_at_semester',
        'available_until_tahun',
        'available_until_semester',
        'is_expired',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (Bimbingan $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
