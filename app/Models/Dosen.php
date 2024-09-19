<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $fillable = [
        'uuid',
        'user_id',
        'nid',
        'nama',
        'gelar',
        'program_studi_id',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (Dosen $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function researchDosen(): HasMany
    {
        return $this->hasMany(ResearchDosen::class);
    }

    public function penilaiPertama(): HasMany
    {
        return $this->hasMany(ProposalSkripsi::class, 'penilai1');
    }

    public function penilaiKedua(): HasMany
    {
        return $this->hasMany(ProposalSkripsi::class, 'penilai2');
    }

    public function penilaiKetiga(): HasMany
    {
        return $this->hasMany(ProposalSkripsi::class, 'penilai3');
    }

    public function penilaiPertamaRevisiProposal(): HasMany
    {
        return $this->hasMany(RevisiProposal::class, 'penilai1');
    }

    public function penilaiKeduaRevisiProposal(): HasMany
    {
        return $this->hasMany(RevisiProposal::class, 'penilai2');
    }

    public function penilaiKetigaRevisiProposal(): HasMany
    {
        return $this->hasMany(RevisiProposal::class, 'penilai3');
    }

    public function pembimbingPertamaLaporanAkhir(): BelongsTo
    {
        return $this->belongsTo(LaporanAkhir::class, 'pembimbing1');
    }

    public function pembimbingKeduaLaporanAkhir(): BelongsTo
    {
        return $this->belongsTo(LaporanAkhir::class, 'pembimbing2');
    }
}