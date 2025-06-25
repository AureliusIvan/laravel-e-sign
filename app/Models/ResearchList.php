<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchList extends Model
{
    use HasFactory;

    protected $table = 'research_list';

    protected $fillable = [
        'uuid',
        'program_studi_id',
        'topik_penelitian',
        'kode_penelitian',
        'deskripsi',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ResearchList $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function researchDosen(): HasMany
    {
        return $this->hasMany(ResearchDosen::class);
    }

    public function areaPenelitian(): HasMany
    {
        return $this->hasMany(AreaPenelitian::class);
    }

    public function topikPenelitianProposal(): HasMany
    {
        return $this->hasMany(TopikPenelitianProposal::class);
    }
}