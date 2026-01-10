<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_voided')->default(false)->after('notes');
            $table->text('void_reason')->nullable()->after('is_voided');
            $table->unsignedBigInteger('voided_by')->nullable()->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');

            // Foreign key for voided_by
            $table->foreign('voided_by')->references('id')->on('users')->nullOnDelete();
        });

        // Migrate existing VOIDED notes to the new is_voided field
        DB::statement("
            UPDATE sales
            SET is_voided = 1,
                voided_at = updated_at
            WHERE notes LIKE '%VOIDED%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['is_voided', 'void_reason', 'voided_by', 'voided_at']);
        });
    }
};
