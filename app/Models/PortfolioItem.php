<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioItem extends Model
{
    /** Tab key => label. */
    public const TYPES = [
        'website' => 'Websites & Software',
        'video' => 'Video Ads',
        'graphic' => 'Graphics',
        'automation' => 'Automations',
        'article' => 'Articles',
    ];

    protected $fillable = [
        'type', 'title', 'description', 'url', 'image_path', 'preview_image',
        'credentials', 'is_active', 'sort_order', 'uploaded_by',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Many images — used by Automations (one automation, many screenshots). */
    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function label(string $type): string
    {
        return self::TYPES[$type] ?? ucfirst($type);
    }

    public function imageUrl(): ?string
    {
        // Root-relative so it works on any host/port (localhost:8000, Apache, a domain)
        // regardless of APP_URL.
        return $this->image_path ? '/storage/' . ltrim($this->image_path, '/') : null;
    }

    /** Thumbnail for an item — an uploaded image wins, else the fetched link preview. */
    public function thumbnail(): ?string
    {
        return $this->imageUrl() ?: $this->preview_image;
    }

    /** A graphic shows its uploaded image if present, otherwise the embedded Instagram post. */
    public function hasVisual(): bool
    {
        return (bool) ($this->image_path || $this->instagramEmbedUrl());
    }

    /** Convert an Instagram reel/post URL into an embeddable iframe URL. */
    public function instagramEmbedUrl(): ?string
    {
        if (! $this->url) {
            return null;
        }

        if (preg_match('#instagram\.com/(reel|reels|p|tv)/([A-Za-z0-9_\-]+)#i', $this->url, $m)) {
            $kind = strtolower($m[1]) === 'reels' ? 'reel' : strtolower($m[1]);

            return "https://www.instagram.com/{$kind}/{$m[2]}/embed";
        }

        return null;
    }
}
