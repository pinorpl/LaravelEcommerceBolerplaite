<?php

namespace App\Modules\ProductCatalog\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\UserManagement\Domain\Models\User;

/**
 * Product – Aggregate Root for the ProductCatalog bounded context.
 *
 * The slug is the public identifier used in URLs (SEO-friendly).
 * price uses decimal cast to avoid floating-point precision issues.
 * created_by tracks which admin created the product.
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'stock'     => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** The admin who created this product */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Scope: only active products visible to buyers */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Scope: search by name or description */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
