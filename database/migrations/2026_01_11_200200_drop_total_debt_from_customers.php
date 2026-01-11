<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the total_debt column as it's now calculated from transactions
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('total_debt');
        });
    }

    public function down(): void
    {
        // Restore the column if rollback is needed
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('total_debt', 12, 2)->default(0.00)->after('address');
        });
    }
};
