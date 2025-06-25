<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'permintaan_mahasiswa';

    protected $fillable = [
        'uuid',
        'permintaan_mahasiswa_form_id',
        'mahasiswa_id',
        'dosen_id',
        'research_list_id',
        'is_rti',
        'is_uploaded',
        'file_pendukung',
        'file_pendukung_random',
        'note_mahasiswa',
        'status_pembimbing',
        'status',
        'note_dosen',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (PermintaanMahasiswa $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function permintaanMahasiswaForm(): BelongsTo
    {
        return $this->belongsTo(PermintaanMahasiswaForm::class);
    }
}
