<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResearchDosen extends Model
{
    use HasFactory;

    protected $table = 'research_dosen';

    protected $fillable = [
        'uuid',
        'program_studi_id',
        'dosen_id',
        'research_list_id',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ResearchDosen $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function researchList(): BelongsTo
    {
        return $this->belongsTo(ResearchList::class);
    }
}
