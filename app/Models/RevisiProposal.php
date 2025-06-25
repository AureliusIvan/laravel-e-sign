<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RevisiProposal extends Model
{
    use HasFactory;

    protected $table = 'revisi_proposal';

    protected $fillable = [
        'uuid',
        'proposal_skripsi_id',
        'revisi_proposal_form_id',
        'mahasiswa_id',
        'judul_revisi_proposal',
        'file_revisi_proposal',
        'file_revisi_proposal_random',
        'status',
        'penilai1',
        'file_revisi_penilai1',
        'file_revisi_random_penilai1',
        'status_revisi_approval_penilai1',
        'note_revisi_penilai1',
        'tanggal_approval_revisi_penilai1',
        'penilai2',
        'file_revisi_penilai2',
        'file_revisi_random_penilai2',
        'status_revisi_approval_penilai2',
        'note_revisi_penilai2',
        'tanggal_approval_revisi_penilai2',
        'penilai3',
        'file_revisi_penilai3',
        'file_revisi_random_penilai3',
        'status_revisi_approval_penilai3',
        'note_revisi_penilai3',
        'tanggal_approval_revisi_penilai3',
        'status_akhir',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (RevisiProposal $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function revisiProposalForm(): BelongsTo
    {
        return $this->belongsTo(RevisiProposalForm::class);
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

    public function proposalSkripsi(): BelongsTo
    {
        return $this->belongsTo(ProposalSkripsi::class);
    }

    public function laporanAkhir(): HasMany
    {
        return $this->hasMany(LaporanAkhir::class);
    }
}
