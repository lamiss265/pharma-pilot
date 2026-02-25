<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Batch/lot tracking
            $table->string('batch_number')->nullable()->after('barcode');
            $table->date('manufacturing_date')->nullable()->after('batch_number');
            
            // Automated reorder points
            $table->integer('reorder_point')->default(5)->after('quantity');
            $table->integer('optimal_stock_level')->default(20)->after('reorder_point');
            
            // Detailed product information
            $table->string('dci')->nullable()->after('name')->comment('International Common Denomination');
            $table->string('dosage_form')->nullable()->after('dci')->comment('Tablet, Syrup, Injection, etc.');
            $table->string('therapeutic_class')->nullable()->after('dosage_form');
            $table->text('composition')->nullable()->after('therapeutic_class');
            $table->text('indications')->nullable()->after('composition');
            $table->text('contraindications')->nullable()->after('indications');
            $table->text('side_effects')->nullable()->after('contraindications');
            $table->string('storage_conditions')->nullable()->after('side_effects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'batch_number',
                'manufacturing_date',
                'reorder_point',
                'optimal_stock_level',
                'dci',
                'dosage_form',
                'therapeutic_class',
                'composition',
                'indications',
                'contraindications',
                'side_effects',
                'storage_conditions'
            ]);
        });
    }
}; 