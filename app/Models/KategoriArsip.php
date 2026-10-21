<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KategoriArsip extends Model
{
    protected $fillable = [
        'nama_bidang',
        'kode_bidang',
        'deskripsi',
        'parent_id',
    ];

    /**
     * Relasi ke parent kategori (untuk tree view)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(KategoriArsip::class, 'parent_id');
    }

    /**
     * Relasi ke child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(KategoriArsip::class, 'parent_id');
    }

    /**
     * Relasi ke arsip data
     */
    public function arsipData(): HasMany
    {
        return $this->hasMany(ArsipData::class, 'kategori_id');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get full path kategori (untuk breadcrumb)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->nama_bidang];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->nama_bidang);
            $parent = $parent->parent;
        }

        return implode(' / ', $path);
    }

    /**
     * Scope untuk root categories (tanpa parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
