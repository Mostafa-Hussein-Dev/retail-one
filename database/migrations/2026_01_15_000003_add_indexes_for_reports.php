<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_date');
            $table->index(['is_voided', 'sale_date']);
            $table->index(['customer_id', 'is_voided']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('purchase_date');
            $table->index(['is_voided', 'purchase_date']);
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->index('return_date');
            $table->index(['is_voided', 'return_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['sale_date']);
            $table->dropIndex(['is_voided', 'sale_date']);
            $table->dropIndex(['customer_id', 'is_voided']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['purchase_date']);
            $table->dropIndex(['is_voided', 'purchase_date']);
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->dropIndex(['return_date']);
            $table->dropIndex(['is_voided', 'return_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'quantity']);
        });
    }
};
