<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProposalSkripsiForm extends Model
{
    use HasFactory;

    protected $table = 'proposal_skripsi_form';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'judul_form',
        'keterangan',
        'dibuka',
        'ditutup',
        'deadline_penilaian',
        'publish_dosen',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ProposalSkripsiForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalSkripsi(): HasMany
    {
        return $this->hasMany(ProposalSkripsi::class);
    }

    public function revisiProposalFrom(): HasMany
    {
        return $this->hasMany(RevisiProposalForm::class);
    }
}
