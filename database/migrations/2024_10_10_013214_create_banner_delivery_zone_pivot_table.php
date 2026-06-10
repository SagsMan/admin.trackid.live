<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBannerDeliveryZonePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_delivery_zone', function (Blueprint $table) {
            $table->unsignedBigInteger('banner_id')->index();
            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('cascade');
            $table->unsignedBigInteger('delivery_zone_id')->index();
            $table->foreign('delivery_zone_id')->references('id')->on('delivery_zones')->onDelete('cascade');
            $table->primary(['banner_id', 'delivery_zone_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banner_delivery_zone');
    }
}
