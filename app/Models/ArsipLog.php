<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArsipLog extends Model
{
    protected $table = 'arsip_log';

    protected $fillable = [
        'arsip_id',
        'user_id',
        'aksi',
        'keterangan',
    ];

    /**
     * Relasi ke arsip data
     */
    public function arsip(): BelongsTo
    {
        return $this->belongsTo(ArsipData::class, 'arsip_id');
    }

    /**
     * Relasi ke user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk filter aksi
     */
    public function scopeAksi($query, $aksi)
    {
        return $query->where('aksi', $aksi);
    }

    /**
     * Scope untuk filter user
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter tanggal
     */
    public function scopeTanggal($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
