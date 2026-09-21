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
            $table->double('discount')->nullable()->after('address');
            $table->double('discount_amount')->nullable()->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wp_orders', function (Blueprint $table) {
            $table->dropColumn('discount', 'discount_amount');
        });
    }
};
