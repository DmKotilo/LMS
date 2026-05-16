<?php

namespace City\Models;
use Address\Model\Address;
use App\Modules\City\src\Models\CityProductPrice;
use App\Traits\Localization\HasTranslate;
use Delivery\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Promotion\Models\Promotion;


class City extends Model
{
    use HasFactory, HasTranslate;

    protected $fillable = [
        'uuid_1c',
        'name',
        'latitude',
        'longitude',
        'is_active',
        'pickup_bonus_points',
        'position',
    ];

    public static function scopeActive($q) : Builder
    {
        return $q->where('is_active', true);
    }

    public static function scopeOrdered($q): Builder
    {
        // Сортировка по position по возрастанию, NULL — в конце
        return $q
            ->orderByRaw('position is null, position asc')
            ->orderBy('name', 'asc');
    }
    public static function getTransaledField(): array
    {
        return [
            'name' => ['title'=>'Название', 'type'=>'text'],
        ];
    }
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_city');
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }
    public function productPrices()
    {
        return $this->hasMany(CityProductPrice::class);
    }



    public function pickupLocations()
    {
        return $this->hasMany(\Delivery\Models\PickupLocation::class, 'city_id');
    }
}
