<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wp_orders', function (Blueprint $table) {
            $table->integer('order_type')->after('discount_amount');
            $table->double('delivery_charge')->default(0)->after('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wp_orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'delivery_charge']);
        });
    }
};
