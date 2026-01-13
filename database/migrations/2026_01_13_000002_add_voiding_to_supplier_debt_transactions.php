<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_debt_transactions', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('description');
            $table->text('void_reason')->nullable()->after('voided_at');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_debt_transactions', function (Blueprint $table) {
            $table->dropColumn(['voided_at', 'void_reason']);
        });
    }
};
