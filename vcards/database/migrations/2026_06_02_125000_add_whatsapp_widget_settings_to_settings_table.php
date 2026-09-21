<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $whatsappWidgetShow = Setting::where('key', 'whatsapp_widget_show')->first();
        if (!$whatsappWidgetShow) {
            Setting::create([
                'key' => 'whatsapp_widget_show',
                'value' => '0',
            ]);
        }

        $whatsappWidgetMessage = Setting::where('key', 'whatsapp_widget_message')->first();
        if (!$whatsappWidgetMessage) {
            Setting::create([
                'key' => 'whatsapp_widget_message',
                'value' => '<p>Hello! I’m interested in your Digital vCard Services. Can you please provide more information?</p>',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['whatsapp_widget_show', 'whatsapp_widget_message'])->delete();
    }
};
