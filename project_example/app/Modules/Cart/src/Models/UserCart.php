<?php

namespace Cart\Models;
use App\Modules\Cart\src\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use List\Models\WhitelistItem;
use User\Models\User;


class UserCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id')->orderBy('id', 'desc');
    }

    public function scopeGuest(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    public function scopeWithUser(Builder $query): void
    {
        $query->whereNotNull('user_id');
    }

    public function scopeWithoutItems(Builder $query): void
    {
        $query->doesntHave('items');
    }

    public function scopeWithItems(Builder $query): void
    {
        $query->has('items');
    }

    public function getUserDisplayNameAttribute(): string
    {
        return $this->user?->name ?? 'Гость';
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->count();
    }


    // В модели UserCart
    public function getItemsListAttribute(): string
    {
        return $this->items->map(fn($i) => ($i->item?->name ?? '[не найден]') . ' × ' . $i->quantity)
            ->implode(', ');
    }


}
