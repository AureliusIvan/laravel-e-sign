<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalSidang extends Model
{
    use HasFactory;

    protected $table = 'jadwal_sidang';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'laporan_akhir_id',
        'mahasiswa_id',
        'jadwal_sidang',
        'ruang_sidang',
        'pembimbing1',
        'file_pembimbing1',
        'file_random_pembimbing1',
        'keputusan_sidang_pembimbing1',
        'berita_acara_pembimbing1',
        'pembimbing2',
        'file_pembimbing2',
        'file_random_pembimbing2',
        'keputusan_sidang_pembimbing2',
        'berita_acara_pembimbing2',
        'ketua_sidang',
        'file_ketua_sidang',
        'file_random_ketua_sidang',
        'keputusan_sidang_ketua_sidang',
        'berita_acara_ketua_sidang',
        'penguji',
        'file_penguji',
        'file_random_penguji',
        'keputusan_sidang_penguji',
        'berita_acara_penguji',
        'keputusan_akhir',
        'ganti_judul',
        'pengumpulan_laporan_dibuka',
        'pengumpulan_laporan_ditutup',

    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (JadwalSidang $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function laporanAkhir(): BelongsTo
    {
        return $this->belongsTo(LaporanAkhir::class);
    }

    public function pembimbingPertama(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing1');
    }

    public function pembimbingKedua(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing2');
    }

    public function pengujiSidang(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penguji');
    }

    public function ketuaSidang(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'ketua_sidang');
    }
}
