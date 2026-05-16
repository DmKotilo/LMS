<?php

namespace Address\Model;

use City\Models\City;
use Delivery\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use User\Models\User;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        "dadata_json",
        "postal_code",
        "region",
        "city_dadata",
        "city_id",
        "street",
        "house",
        "entrance",
        "floor",
        "delivery_zone_id",
        "apartment",
        "car_pass",
        "is_primary",
        "value_dadata",
        "final_address",
        "user_deleted",
        "user_id",
    ];

    protected $casts = [
        "dadata_json" => 'array',
        "car_pass"    => 'boolean',
        "is_primary"  => 'boolean',
    ];
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function formatFinalAddress(array $addr): string
    {
        $parts = [];

        // Почтовый индекс
        if (!empty($addr['postal_code'])) {
            $parts[] = $addr['postal_code'];
        }

        $parts[] = $addr['value_dadata'];

        /*// Город (берём из city_id) или city_dadata
        if (!empty($addr['city_id'])) {
            $city = City::find($addr['city_id']);
            if ($city && $city?->name != 'Другой город') {
                $parts[] = 'г. ' . $city->name;
            } else {
                $parts[] = $addr['city_dadata'];
            }
        }

        // Улица
        if (!empty($addr['street'])) {
            $parts[] = 'ул. ' . $addr['street'];
        }

        // Дом
        if (!empty($addr['house'])) {
            $parts[] = $addr['house'];
        }*/

        // Этаж
        if (!empty($addr['floor'])) {
            $parts[] = 'эт. ' . $addr['floor'];
        }

        // Квартира
        if (!empty($addr['apartment'])) {
            $parts[] = 'кв. ' . $addr['apartment'];
        }

        return implode(', ', $parts);
    }


    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }


}
