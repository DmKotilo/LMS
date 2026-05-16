<?php

namespace Address\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Order\Models\Order;

class ContactMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'contact_method_order')
            ->withTimestamps();
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'contact_method_user');
    }
}
