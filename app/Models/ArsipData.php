<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArsipData extends Model
{
    protected $fillable = [
        'judul_arsip',
        'jenis_arsip',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'tgl_kegiatan',
        'deskripsi',
        'user_id',
        'kategori_id',
    ];

    protected $casts = [
        'tgl_kegiatan' => 'date',
        'file_size' => 'integer',
    ];

    /**
     * Relasi ke user yang mengupload
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke kategori arsip
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArsip::class, 'kategori_id');
    }

    /**
     * Relasi ke metadata
     */
    public function metadata(): HasMany
    {
        return $this->hasMany(ArsipMetadata::class, 'arsip_id');
    }

    /**
     * Relasi ke log aktivitas
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ArsipLog::class, 'arsip_id');
    }

    /**
     * Get file URL untuk download
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Format ukuran file
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes == 0) return '0 B';

        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Scope untuk filter berdasarkan jenis arsip
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis_arsip', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('judul_arsip', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }
}
