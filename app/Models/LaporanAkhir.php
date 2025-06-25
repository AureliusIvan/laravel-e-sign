<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanAkhir extends Model
{
    use HasFactory;

    protected $table = 'laporan_akhir';

    protected $fillable = [
        'uuid',
        'laporan_akhir_form_id',
        'revisi_proposal_id',
        'mahasiswa_id',
        'judul_laporan',
        'file_laporan',
        'file_laporan_random',
        'status',
        'pembimbing1',
        'file_pembimbing1',
        'file_random_pembimbing1',
        'status_approval_pembimbing1',
        'note_pembimbing1',
        'tanggal_approval_pembimbing1',
        'pembimbing2',
        'file_pembimbing2',
        'file_random_pembimbing2',
        'status_approval_pembimbing2',
        'note_pembimbing2',
        'tanggal_approval_pembimbing2',
        'file_kaprodi',
        'file_random_kaprodi',
        'status_approval_kaprodi',
        'note_kaprodi',
        'tanggal_approval_kaprodi',
        'status_akhir',
        'ganti_judul',
        'pengumpulan_laporan_dibuka',
        'pengumpulan_laporan_ditutup',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (LaporanAkhir $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function laporanAkhirForm(): BelongsTo
    {
        return $this->belongsTo(LaporanAkhirForm::class);
    }

    public function revisiProposal(): BelongsTo
    {
        return $this->belongsTo(RevisiProposal::class);
    }

    public function pembimbingPertama(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing1');
    }

    public function pembimbingKedua(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing2');
    }

    public function jadwalSidang(): HasMany
    {
        return $this->hasMany(JadwalSidang::class);
    }
}
