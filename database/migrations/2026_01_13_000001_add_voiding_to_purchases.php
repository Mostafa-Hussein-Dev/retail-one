<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('is_voided')->default(false)->after('notes');
            $table->text('void_reason')->nullable()->after('is_voided');
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('set null')->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['is_voided', 'void_reason', 'voided_by', 'voided_at']);
        });
    }
};
