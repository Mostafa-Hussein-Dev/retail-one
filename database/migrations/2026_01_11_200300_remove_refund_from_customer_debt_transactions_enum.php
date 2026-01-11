<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, delete any existing refund transactions since we're removing this type
        DB::table('customer_debt_transactions')
            ->where('transaction_type', 'refund')
            ->delete();

        // Remove 'refund' from the enum
        DB::statement("ALTER TABLE customer_debt_transactions MODIFY COLUMN transaction_type ENUM('debt', 'payment') NOT NULL");
    }

    public function down(): void
    {
        // Add 'refund' back to the enum
        DB::statement("ALTER TABLE customer_debt_transactions MODIFY COLUMN transaction_type ENUM('debt', 'payment', 'refund') NOT NULL");
    }
};
