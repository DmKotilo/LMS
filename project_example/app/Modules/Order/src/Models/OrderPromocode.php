<?php

namespace Order\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Order\Enums\OrderPromocodeStatusEnum;
use Order\Models\Order;
use Order\Models\Transaction;
use Product\Models\Product;


class OrderPromocode extends Model
{
    use HasFactory;

    protected $table = 'order_promocodes';

    protected $fillable = [
        'promocode',
        'description',
        'date_from',
        'date_to',
        'exceeded_limit',
        'number_applications',
        'percent',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'status' => OrderPromocodeStatusEnum::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function($item)
        {
            $item->status = $item->updateStatus();
            // Виртуальное поле из админки (сумма заказов) — не колонка БД
            $item->offsetUnset('orders_sum_price_final');

            return $item;
        });
        static::saved(function($item)
        {

            return $item;
        });
    }

    public function checkValidated(bool $increaseUsage = true): bool
    {
        $this->status = $this->updateStatus();

        if ($this->status == OrderPromocodeStatusEnum::active) {

            // увеличиваем количество использований только при заказе
            if ($increaseUsage == true) {
                $this->number_applications = ($this->number_applications ?? 0) + 1;

                if ($this->exceeded_limit !== null && $this->number_applications >= $this->exceeded_limit) {
                    $this->status = OrderPromocodeStatusEnum::exceeded_limit;
                }
            }
        }

        $this->save();

        return $this->status == OrderPromocodeStatusEnum::active;
    }


    public function updateStatus() : OrderPromocodeStatusEnum {

        $result = OrderPromocodeStatusEnum::active;
        if($this->status?->value == OrderPromocodeStatusEnum::inactive->value) {
            $result = OrderPromocodeStatusEnum::inactive;
        } else {
            if($this->exceeded_limit != null && $this->number_applications >= $this->exceeded_limit) {
                $result = OrderPromocodeStatusEnum::exceeded_limit;
            }
/*            dd(Carbon::now(), $this->date_from,Carbon::now()->lessThan($this->date_from));*/
            if($this->date_from != null && Carbon::now()->lessThan($this->date_from)) {
                $result = OrderPromocodeStatusEnum::expired;
            }
            if($this->date_to != null && !Carbon::now()->lessThan($this->date_to)) {
                $result = OrderPromocodeStatusEnum::expired;
            }
        }

        return $result;
    }

    /**
     * Заказы, в которых был использован этот промокод.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_promocode_id');
    }

}
