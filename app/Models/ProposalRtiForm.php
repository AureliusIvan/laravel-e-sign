<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalRtiForm extends Model
{
    use HasFactory;

    protected $table = 'proposal_skripsi_rti_form';

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'program_studi_id',
        'judul_form',
        'keterangan',
        'dibuka',
        'ditutup',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ProposalRtiForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function proposalRti(): HasMany
    {
        return $this->hasMany(ProposalSkripsiRTI::class, 'proposal_skripsi_rti_form_id');
    }
}
