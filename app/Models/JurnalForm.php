<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JurnalForm extends Model
{
    use HasFactory;

    protected $table = 'jurnal_form';

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
        static::creating(function (ProposalSkripsiForm $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
