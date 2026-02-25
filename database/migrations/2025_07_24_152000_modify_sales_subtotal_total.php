<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Ensure subtotal and total_amount have defaults to avoid missing value errors
            $table->decimal('subtotal', 10, 2)->default(0)->change();
            $table->decimal('total_amount', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(null)->nullable(false)->change();
            $table->decimal('total_amount', 10, 2)->default(null)->nullable(false)->change();
        });
    }
};
