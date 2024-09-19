<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrioritasSemester extends Model
{
    use HasFactory;

    protected $table = 'prioritas_semester';

    protected $fillable = [
        'uuid',
        'semester',
        'prioritas',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (PrioritasSemester $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
