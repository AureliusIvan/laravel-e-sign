<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermintaanMahasiswaForm extends Model
{
    use HasFactory;

    protected $table = 'permintaan_mahasiswa_form';

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
        static::creating(function (PermintaanMahasiswaForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function permintaanMahasiswa(): HasMany
    {
        return $this->hasMany(PermintaanMahasiswa::class);
    }
}