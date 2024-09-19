<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';

    protected $fillable = ['program_studi'];

    protected $hidden = ['id'];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (ProgramStudi $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class);
    }

    public function pengaturan(): HasMany
    {
        return $this->hasMany(Pengaturan::class);
    }

    public function beritaAcara(): HasMany
    {
        return $this->hasMany(BeritaAcara::class);
    }

    public function researchList(): HasMany
    {
        return $this->hasMany(ResearchList::class);
    }

    public function researchDosen(): HasMany
    {
        return $this->hasMany(ResearchDosen::class);
    }
}
