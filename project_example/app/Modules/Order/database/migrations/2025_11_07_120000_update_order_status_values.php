<?php

use App\Modules\Order\src\Enums\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => OrderStatusEnum::InProcessing->value]);

        DB::table('orders')
            ->where('status', 'approved')
            ->update(['status' => OrderStatusEnum::Shipped->value]);

        DB::table('orders')
            ->where('status', 'delivered')
            ->update(['status' => OrderStatusEnum::Completed->value]);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', OrderStatusEnum::InProcessing->value)
            ->update(['status' => 'pending']);

        DB::table('orders')
            ->where('status', OrderStatusEnum::Shipped->value)
            ->update(['status' => 'approved']);

        DB::table('orders')
            ->where('status', OrderStatusEnum::Completed->value)
            ->update(['status' => 'delivered']);
    }
};

