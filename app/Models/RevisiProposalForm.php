<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RevisiProposalForm extends Model
{
    use HasFactory;

    protected $table = 'revisi_proposal_form';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'proposal_skripsi_form_id',
        'judul_form',
        'keterangan',
        'dibuka',
        'ditutup',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (RevisiProposalForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalSkripsiForm(): BelongsTo
    {
        return $this->belongsTo(ProposalSkripsiForm::class);
    }

    public function revisiProposal(): HasMany
    {
        return $this->hasMany(RevisiProposal::class);
    }
}