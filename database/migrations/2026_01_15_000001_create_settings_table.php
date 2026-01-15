<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'integer', 'boolean', 'decimal', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Default settings
        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'Hijazi Store', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_address', 'value' => 'Beirut, Lebanon', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '+961 123 456 789', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_tax_id', 'value' => '', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'exchange_rate_usd_lbp', 'value' => '89500', 'type' => 'decimal', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pagination_per_page', 'value' => '20', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'receipt_footer', 'value' => 'شكراً لزيارتكم - نتمنى رؤيتكم مجدداً', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'receipt_show_logo', 'value' => '1', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'receipt_auto_print', 'value' => '0', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'backup_enabled', 'value' => '1', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'backup_time', 'value' => '02:00', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'backup_retention_days', 'value' => '30', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
