<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'uuid',
        'user_id',
        'nim',
        'nama',
        'program_studi_id',
        'angkatan',
        'status_aktif_skripsi',
        'tahun_ajaran_id',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (Mahasiswa $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposalSkripsi(): HasMany
    {
        return $this->hasMany(ProposalSkripsi::class);
    }

    public function revisiProposalSkripsi(): HasMany
    {
        return $this->hasMany(RevisiProposal::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }
}