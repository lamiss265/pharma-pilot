<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropQuantityFromSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('sales', 'quantity')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('sales', 'quantity')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->integer('quantity')->after('receipt_number')->default(0);
            });
        }
    }
}
