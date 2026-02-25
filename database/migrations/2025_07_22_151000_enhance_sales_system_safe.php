<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceSalesSystemSafe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Enhance sales table - add columns that don't exist
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'receipt_number')) {
                $table->string('receipt_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('sales', 'discount_type')) {
                $table->string('discount_type')->nullable();
            }
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'final_amount')) {
                $table->decimal('final_amount', 8, 2);
            }
            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method')->default('cash');
            }
            if (!Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('sales', 'is_prescription')) {
                $table->boolean('is_prescription')->default(false);
            }
            if (!Schema::hasColumn('sales', 'prescription_number')) {
                $table->string('prescription_number')->nullable();
            }
            if (!Schema::hasColumn('sales', 'status')) {
                $table->string('status')->default('completed');
            }
        });

        // Create sale_items table for multi-item sales
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
                $table->integer('quantity');
                $table->decimal('unit_price', 8, 2);
                $table->decimal('discount_amount', 8, 2)->default(0);
                $table->decimal('subtotal', 8, 2);
                $table->timestamps();
            });
        }

        // Create customers table (enhanced client management)
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('customer_number')->unique();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->decimal('loyalty_points', 8, 2)->default(0);
                $table->decimal('total_spent', 10, 2)->default(0);
                $table->integer('total_purchases')->default(0);
                $table->string('preferred_language', 2)->default('en');
                $table->boolean('email_notifications')->default(true);
                $table->boolean('sms_notifications')->default(true);
                $table->timestamps();
            });
        }

        // Create promotions table
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique()->nullable();
                $table->enum('type', ['percentage', 'fixed', 'bogo', 'loyalty']);
                $table->decimal('value', 8, 2); // percentage or fixed amount
                $table->decimal('minimum_amount', 8, 2)->nullable();
                $table->integer('minimum_items')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_active')->default(true);
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_count')->default(0);
                $table->json('applicable_products')->nullable(); // product IDs
                $table->json('applicable_categories')->nullable(); // category IDs
                $table->timestamps();
            });
        }

        // Create receipts table
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->onDelete('cascade');
                $table->string('receipt_number')->unique();
                $table->text('receipt_content'); // HTML content
                $table->string('format')->default('pdf'); // pdf, thermal
                $table->boolean('emailed')->default(false);
                $table->boolean('sms_sent')->default(false);
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();
            });
        }

        // Create barcode_scans table for tracking
        if (!Schema::hasTable('barcode_scans')) {
            Schema::create('barcode_scans', function (Blueprint $table) {
                $table->id();
                $table->string('barcode');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->boolean('found')->default(false);
                $table->timestamp('scanned_at');
                $table->timestamps();
            });
        }

        // Add customer_id to sales table if it doesn't exist
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barcode_scans');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('sale_items');
        
        Schema::table('sales', function (Blueprint $table) {
            $columnsToCheck = [
                'receipt_number', 'discount_type', 'tax_amount',
                'final_amount', 'payment_method', 'notes', 'is_prescription',
                'prescription_number', 'status', 'customer_id'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
