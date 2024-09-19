<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RevisiLaporan extends Model
{
    use HasFactory;

    protected $table = 'revisi_laporan';

    protected $fillable = [
        'uuid',
        'revisi_laporan_form',
        'laporan_akhir_form',
        'judul_revisi_laporan',
        'file_revisi_laporan',
        'status',
        'penguji',
        'file_penguji',
        'file_random_penguji',
        'status_approval_penguji',
        'note_penguji',
        'tanggal_approval_penguji',
        'ketua_sidang',
        'file_ketua_sidang',
        'file_random_ketua_sidang',
        'status_approval_ketua_sidang',
        'note_ketua_sidang',
        'tanggal_approval_ketua_sidang',
        'pembimbing1',
        'file_pembimbing1',
        'file_random_pembimbing1',
        'status_approval_pembimbing1',
        'note_pembimbing1',
        'tanggal_approval_pembimbing1',
        'pembimbing2',
        'file_pembimbing2',
        'file_random_pembimbing2',
        'status_approval_pembimbing2',
        'note_pembimbing2',
        'tanggal_approval_pembimbing2',
        'file_kaprodi',
        'file_random_kaprodi',
        'status_approval_kaprodi',
        'note_kaprodi',
        'tanggal_approval_kaprodi',
        'status_akhir',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (RevisiLaporan $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }
}
