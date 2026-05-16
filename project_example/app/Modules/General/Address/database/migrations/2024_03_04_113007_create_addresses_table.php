<?php

use City\Model\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public $model = \Address\Model\Address::class;
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->text("dadata_json")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("region")->nullable();
            $table->string("city_dadata")->nullable();
            $table->foreignId("city_id")->nullable()->constrained()->onDelete('cascade');
            $table->foreignId("user_id")->nullable()->constrained()->onDelete('cascade');
            $table->string("street")->nullable();
            $table->string("house")->nullable();
            $table->string("entrance")->nullable();
            $table->string("floor")->nullable();
            $table->string("apartment")->nullable();
            $table->boolean("car_pass")->default(false);
            $table->boolean("is_primary")->default(false);
            $table->boolean("user_deleted")->default(false);
            $table->text("value_dadata")->nullable();
            $table->text("final_address")->nullable();
            $table->timestamps();
        });
    }

    //клиенты должны вывести

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
