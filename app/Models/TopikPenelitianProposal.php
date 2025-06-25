<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopikPenelitianProposal extends Model
{
    use HasFactory;

    protected $table = 'topik_penelitian_proposal';

    protected $fillable = [
        'uuid',
        'proposal_skripsi_id',
        'research_list_id',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (TopikPenelitianProposal $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalSkripsi(): BelongsTo
    {
        return $this->belongsTo(ProposalSkripsi::class);
    }

    public function researchList(): BelongsTo
    {
        return $this->belongsTo(ResearchList::class);
    }
}
