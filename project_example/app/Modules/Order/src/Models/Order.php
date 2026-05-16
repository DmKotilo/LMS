<?php

namespace Order\Models;
use Address\Model\Address;
use Address\Model\ContactMethod;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Order\src\Enums\OrderStatusEnum;
use App\Notifications\OrderStatusChangedNotification;
use Delivery\Models\PickupLocation;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Order\Enums\OrderDeliveryTypeEnum;
use Order\Observers\OrderObserver;
use User\Models\User;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid_1c',
        'user_id',
        'product_id',
        'combo_id',
        'transaction_method_id',
        'order_promocode_id',
        'delivery_address_id',
        'pickup_location_id',
        'delivery_type',
        'name',
        'phone',
        'last_name', //фамилия
        'second_name',//отчество
        'email',
        'comment',
        'weight',
        'quantity',
        'cart_sum',
        'delivery_price',
        'personal_discount',
        'promocode_discount',
        'second_item_discount',
        'promo',
        'price_final',
        'bonus_spent',
        'price_final_1c',
        'status',
        'delivery_date',
        'delivery_time_to',
        'delivery_time_from',
        'payment_link',
        'bitrix_lead_id', // ID лида в Битрикс24
        'bitrix_deal_id',  // ID сделки в Битрикс24
        'metrica_client_id', // ID клиента в Яндекс.Метрике
    ];


    protected $casts = [
        'status' => OrderStatusEnum::class,
        'delivery_type' => OrderDeliveryTypeEnum::class,
        'delivery_date' => 'date',
    ];

    public function contactMethods(): BelongsToMany
    {
        return $this->belongsToMany(ContactMethod::class, 'contact_method_order')
            ->withTimestamps();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id')->orderBy('id', 'desc');
    }

    public function transactionMethod()
    {
        return $this->belongsTo(TransactionMethod::class);
    }

    public function orderPromocode()
    {
        return $this->belongsTo(OrderPromocode::class);
    }

    public function pickupLocation()
    {
        return $this->belongsTo(PickupLocation::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address_id', 'id');
    }

    public function getDeliveryAddressForAdminAttribute(): ?string
    {
        // если тип — курьер, берём адрес из связанной модели Address
        if ($this->delivery_type?->value === \Order\Enums\OrderDeliveryTypeEnum::courier->value) {
            return $this->deliveryAddress
                ? Address::formatFinalAddress($this->deliveryAddress->toArray())
                : null;
        }

        // если тип — самовывоз, берём адрес пункта
        if ($this->delivery_type?->value === \Order\Enums\OrderDeliveryTypeEnum::pickup->value) {
            // pickupLocation->getFullAddressAttribute() в твоём PickupLocation называется getFullAddressAttribute
            // поэтому свойство full_address доступно как $this->pickupLocation->full_address
            return $this->pickupLocation?->full_address ?? null;
        }

        return null;
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return $this->delivery_type === OrderDeliveryTypeEnum::pickup ? 'Самовывоз' : 'Курьер';
    }


}
