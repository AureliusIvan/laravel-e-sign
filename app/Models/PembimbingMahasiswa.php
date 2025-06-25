<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembimbingMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'pembimbing_mahasiswa';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'mahasiswa',
        'pembimbing1',
        'status_pembimbing1',
        'pembimbing2',
        'status_pembimbing2',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (PembimbingMahasiswa $model) {
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

    public function mahasiswaData(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa');
    }

    public function pembimbing1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing1');
    }

    public function pembimbing2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing2');
    }
}
