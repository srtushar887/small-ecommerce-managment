<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProduct extends Model
{
    use HasFactory;

    protected $table = 'shop_product';

    protected $fillable = [
        'shop_id',
        'product_id',
        'price',
        'details',
        'thumbnail',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product?->base_price ?? 0);
    }

    public function getEffectiveDetailsAttribute(): ?string
    {
        return $this->details ?: $this->product?->description;
    }

    public function getEffectiveThumbnailAttribute(): string
    {
        if (filled($this->thumbnail)) {
            if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
                return $this->thumbnail;
            }

            return asset('uploads/products/'.$this->thumbnail);
        }

        return $this->product?->thumbnail_url ?? asset('assets/images/product-placeholder.svg');
    }
}
