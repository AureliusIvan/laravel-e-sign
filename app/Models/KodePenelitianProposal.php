<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KodePenelitianProposal extends Model
{
    use HasFactory;

    protected $table = 'kode_penelitian_proposal';

    protected $fillable = [
        'uuid',
        'proposal_skripsi_id',
        'area_penelitian_id',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (KodePenelitianProposal $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalSkripsi(): BelongsTo
    {
        return $this->belongsTo(ProposalSkripsi::class);
    }

    public function areaPenelitian(): BelongsTo
    {
        return $this->belongsTo(AreaPenelitian::class);
    }
}
