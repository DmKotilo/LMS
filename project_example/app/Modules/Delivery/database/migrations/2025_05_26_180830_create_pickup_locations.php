<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public $model = \Delivery\Models\PickupLocation::class;
    public function up(): void
    {
        Schema::create('pickup_locations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('phone')->nullable();
            $table->string('working_hours_from')->nullable();
            $table->string('working_hours_to')->nullable();
            $table->string('site')->nullable();
            $table->tinyInteger('delivery_time')->nullable(); // int в днях

            $table->foreignId('city_id')->constrained()->onDelete('cascade');

            $table->text("dadata_json")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("region_dadata")->nullable();
            $table->string("city_dadata")->nullable();
            $table->string("street")->nullable();
            $table->string("house")->nullable();
            $table->string("entrance")->nullable();
            $table->string("floor")->nullable();
            $table->string("apartment")->nullable();
            $table->string("value_dadata")->nullable(); //значение ДАДАТЫ
            $table->string("latitude")->nullable();
            $table->string("longitude")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_locations');
    }
};
