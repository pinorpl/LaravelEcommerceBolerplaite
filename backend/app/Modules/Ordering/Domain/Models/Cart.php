<?php

namespace App\Modules\Ordering\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\UserManagement\Domain\Models\User;

/**
 * Cart – represents a shopping session.
 * Supports both authenticated users (user_id) and guests (session_id).
 * For authenticated users, the cart persists across devices/sessions.
 */
class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Calculate cart total from item snapshots */
    public function getTotal(): float
    {
        return $this->items->sum(fn ($item) => $item->price * $item->quantity);
    }
}
