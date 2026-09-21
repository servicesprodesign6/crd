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
        \App\Models\ShortCode::create([
            'email_template_type' => '14',
            'short_code' => '{ ordertype }',
            'value' => 'Order Type',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\ShortCode::where([
            'email_template_type' => '14',
            'short_code' => '{ ordertype }',
        ])->delete();
    }
};
