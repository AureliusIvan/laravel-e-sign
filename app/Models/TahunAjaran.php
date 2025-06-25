<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'uuid',
        'tahun',
        'semester',
        'status_aktif',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (TahunAjaran $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function pengaturan(): HasMany
    {
        return $this->hasMany(Pengaturan::class);
    }

    public function beritaAcara(): HasMany
    {
        return $this->hasMany(BeritaAcara::class);
    }
}
