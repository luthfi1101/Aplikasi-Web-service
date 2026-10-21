<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArsipMetadata extends Model
{
    protected $table = 'arsip_metadata';

    protected $fillable = [
        'arsip_id',
        'kunci',
        'nilai',
    ];

    /**
     * Relasi ke arsip data
     */
    public function arsip(): BelongsTo
    {
        return $this->belongsTo(ArsipData::class, 'arsip_id');
    }
}
