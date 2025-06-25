<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalSkripsiRTI extends Model
{
    use HasFactory;

    protected $table = 'proposal_skripsi_rti';

    protected $guarded = ['id'];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ProposalSkripsiRTI $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbingPertama(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing1');
    }

    public function pembimbingKedua(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing2');
    }

    public function proposalRtiForm(): BelongsTo
    {
        return $this->belongsTo(ProposalRtiForm::class, 'proposal_skripsi_rti_form_id');
    }
}
