<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KategoriNilaiDetail extends Model
{
    use HasFactory;

    protected $table = 'kategori_nilai_detail';

    protected $fillable = [
        'uuid',
        'kategori_nilai_id',
        'detail_kategori',
        'detail_persentase',
    ];

    public static function booted(): void
    {
        parent::boot();
        static::creating(function (KategoriNilaiDetail $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function kategoriNilai(): BelongsTo
    {
        return $this->belongsTo(KategoriNilai::class);
    }
}
