<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'total_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('total_amount', 10, 2)->after('subtotal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'total_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('total_amount');
            });
        }
    }
};
