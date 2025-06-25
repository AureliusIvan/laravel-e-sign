<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanDetail extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_detail';

    protected $fillable = [
        'uuid',
        'pengaturan_id',
        'kuota_pembimbing_pertama',
        'kuota_pembimbing_kedua',
        'minimum_jumlah_bimbingan',
        'minimum_jumlah_bimbingan_kedua',
        'tahun_rti_tersedia_sampai',
        'semester_rti_tersedia_sampai',
        'tahun_proposal_tersedia_sampai',
        'semester_proposal_tersedia_sampai',
        'penamaan_proposal',
        'penamaan_revisi_proposal',
        'penamaan_laporan',
        'penamaan_revisi_laporan',
        'jumlah_setuju_proposal',
        'jumlah_setuju_sidang_satupembimbing',
        'jumlah_setuju_sidang_duapembimbing',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (PengaturanDetail $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function pengaturan(): BelongsTo
    {
        return $this->belongsTo(Pengaturan::class);
    }
}
