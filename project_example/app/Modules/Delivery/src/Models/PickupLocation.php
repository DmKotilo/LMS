<?php

namespace Delivery\Models;
use Address\Model\Address;
use App\Modules\Cart\src\Models\CartItem;
use City\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use List\Models\WhitelistItem;
use User\Models\User;


class PickupLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'title',
        'phone',
        'working_hours_from',
        'working_hours_to',
        'site',
        "postal_code",
        "region_dadata", //скрытое поле, подставленное из ответа dadata
        "entrance",
        "floor",
        "apartment",
        "latitude",
        "longitude",
        "delivery_time", //int в днях
        "city_dadata", //скрытое поле, подставленное из ответа dadata
        "value_dadata", //значение, выбранное в dadata
        "dadata_json", //полный ответ dadata
    ];

    public function city_relation()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->postal_code,
/*            $this->region,*/
            $this->city_dadata,
            $this->street,
            $this->house ? 'д. ' . $this->house : null,
            $this->entrance ? 'подъезд ' . $this->entrance : null,
            $this->floor ? 'этаж ' . $this->floor : null,
            $this->apartment ? 'кв. ' . $this->apartment : null,
        ];

        return implode(', ', array_filter($parts));
    }



}
