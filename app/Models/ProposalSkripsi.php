<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalSkripsi extends Model
{
    use HasFactory;

    protected $table = 'proposal_skripsi';

    protected $fillable = [
        'uuid',
        'proposal_skripsi_form_id',
        'mahasiswa_id',
        'judul_proposal',
        'judul_proposal_en',
        'file_proposal',
        'file_proposal_random',
        'file_proposal_mime',
        'status',
        'penilai1',
        'file_penilai1',
        'file_random_penilai1',
        'file_penilai1_mime',
        'status_approval_penilai1',
        'tanggal_approval_penilai1',
        'penilai2',
        'file_penilai2',
        'file_random_penilai2',
        'file_penilai2_mime',
        'status_approval_penilai2',
        'tanggal_approval_penilai2',
        'penilai3',
        'file_penilai3',
        'file_random_penilai3',
        'file_penilai3_mime',
        'status_approval_penilai3',
        'tanggal_approval_penilai3',
        'status_akhir',
        'available_at_tahun',
        'available_at_semester',
        'available_until_tahun',
        'available_until_semester',
        'is_expired',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ProposalSkripsi $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalSkripsiForm(): BelongsTo
    {
        return $this->belongsTo(ProposalSkripsiForm::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function penilaiPertama(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penilai1');
    }

    public function penilaiKedua(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penilai2');
    }

    public function penilaiKetiga(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'penilai3');
    }

    public function topikPenelitianProposal(): HasMany
    {
        return $this->hasMany(TopikPenelitianProposal::class);
    }

    public function kodePenelitianProposal(): HasMany
    {
        return $this->hasMany(KodePenelitianProposal::class);
    }

    public function revisiProposal(): HasMany
    {
        return $this->hasMany(RevisiProposal::class);
    }
}