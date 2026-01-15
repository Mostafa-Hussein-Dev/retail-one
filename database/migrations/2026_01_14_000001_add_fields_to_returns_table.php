<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            // Payment method: how the refund is processed
            $table->enum('payment_method', ['cash_refund', 'debt_reduction', 'mixed'])
                  ->default('cash_refund')
                  ->after('total_return_amount');

            // Amount refunded in cash
            $table->decimal('cash_refund_amount', 12, 2)
                  ->default(0.00)
                  ->after('payment_method');

            // Amount reduced from customer debt
            $table->decimal('debt_reduction_amount', 12, 2)
                  ->default(0.00)
                  ->after('cash_refund_amount');

            // Voiding support (manager only)
            $table->boolean('is_voided')->default(false)->after('reason');
            $table->text('void_reason')->nullable()->after('is_voided');
            $table->foreignId('voided_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn([
                'payment_method',
                'cash_refund_amount',
                'debt_reduction_amount',
                'is_voided',
                'void_reason',
                'voided_by',
                'voided_at'
            ]);
        });
    }
};
