<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AreaPenelitian extends Model
{
    use HasFactory;

    protected $table = 'area_penelitian';

    protected $fillable = [
        'research_list_id',
        'kode_area_penelitian',
        'keterangan',
    ];

    protected $hidden = ['id'];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (AreaPenelitian $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function researchList(): BelongsTo
    {
        return $this->belongsTo(ResearchList::class);
    }

    public function kodePenelitianProposal(): HasMany
    {
        return $this->hasMany(KodePenelitianProposal::class);
    }
}
