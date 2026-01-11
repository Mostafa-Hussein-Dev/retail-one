<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to include 'refund'
        DB::statement("ALTER TABLE customer_debt_transactions MODIFY COLUMN transaction_type ENUM('debt', 'payment', 'refund') NOT NULL");
    }

    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE customer_debt_transactions MODIFY COLUMN transaction_type ENUM('debt', 'payment') NOT NULL");
    }
};
