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
        // Create barcode_scans table if it doesn't exist
        if (!Schema::hasTable('barcode_scans')) {
            Schema::create('barcode_scans', function (Blueprint $table) {
                $table->id();
                $table->string('barcode');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->boolean('found')->default(false);
                $table->timestamp('scanned_at');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['barcode', 'scanned_at']);
            });
        }

        // Create customers table if it doesn't exist
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
                $table->decimal('loyalty_points', 10, 2)->default(0);
                $table->decimal('total_spent', 12, 2)->default(0);
                $table->integer('total_purchases')->default(0);
                $table->string('preferred_language', 2)->default('en');
                $table->boolean('email_notifications')->default(true);
                $table->boolean('sms_notifications')->default(false);
                $table->timestamps();

                $table->index(['customer_number', 'phone', 'email']);
            });
        }

        // Create promotions table if it doesn't exist
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->enum('type', ['percentage', 'fixed', 'buy_x_get_y', 'bulk']);
                $table->decimal('value', 8, 2);
                $table->decimal('minimum_amount', 8, 2)->nullable();
                $table->integer('minimum_items')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_active')->default(true);
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_count')->default(0);
                $table->json('applicable_products')->nullable();
                $table->json('applicable_categories')->nullable();
                $table->timestamps();

                $table->index(['code', 'is_active', 'start_date', 'end_date']);
            });
        }

        // Create receipts table if it doesn't exist
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->string('receipt_number')->unique();
                $table->json('receipt_data');
                $table->string('format')->default('pdf'); // pdf, thermal, email
                $table->boolean('printed')->default(false);
                $table->boolean('emailed')->default(false);
                $table->boolean('sms_sent')->default(false);
                $table->timestamps();

                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                $table->index(['receipt_number', 'sale_id']);
            });
        }

        // Create sale_items table if it doesn't exist
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 8, 2);
                $table->decimal('discount_amount', 8, 2)->default(0);
                $table->decimal('subtotal', 8, 2);
                $table->timestamps();

                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('batch_id')->references('id')->on('batches')->onDelete('set null');
                $table->index(['sale_id', 'product_id']);
            });
        }

        // Create offline_sales table for offline mode
        if (!Schema::hasTable('offline_sales')) {
            Schema::create('offline_sales', function (Blueprint $table) {
                $table->id();
                $table->string('offline_id')->unique();
                $table->json('sale_data');
                $table->unsignedBigInteger('user_id');
                $table->boolean('synced')->default(false);
                $table->timestamp('sale_timestamp');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['offline_id', 'synced', 'user_id']);
            });
        }

        // Create loyalty_transactions table
        if (!Schema::hasTable('loyalty_transactions')) {
            Schema::create('loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted']);
                $table->decimal('points', 8, 2);
                $table->decimal('balance_after', 8, 2);
                $table->string('description')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
                $table->index(['customer_id', 'type', 'created_at']);
            });
        }

        // Create product_barcodes table for multiple barcodes per product
        if (!Schema::hasTable('product_barcodes')) {
            Schema::create('product_barcodes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('barcode')->unique();
                $table->enum('type', ['EAN13', 'UPC', 'CODE128', 'QR', 'CUSTOM']);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->index(['barcode', 'product_id', 'type']);
            });
        }

        // Enhance existing sales table
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'customer_id')) {
                    $table->unsignedBigInteger('customer_id')->nullable()->after('client_id');
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
                }
                if (!Schema::hasColumn('sales', 'promotion_id')) {
                    $table->unsignedBigInteger('promotion_id')->nullable();
                    $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
                }
                if (!Schema::hasColumn('sales', 'loyalty_points_used')) {
                    $table->decimal('loyalty_points_used', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('sales', 'loyalty_points_earned')) {
                    $table->decimal('loyalty_points_earned', 8, 2)->default(0);
                }
                if (!Schema::hasColumn('sales', 'device_id')) {
                    $table->string('device_id')->nullable();
                }
                if (!Schema::hasColumn('sales', 'offline_sale_id')) {
                    $table->string('offline_sale_id')->nullable();
                }
            });
        }

        // Enhance existing products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'active_ingredient')) {
                    $table->string('active_ingredient')->nullable();
                }
                if (!Schema::hasColumn('products', 'brand')) {
                    $table->string('brand')->nullable();
                }
                if (!Schema::hasColumn('products', 'searchable_text')) {
                    $table->text('searchable_text')->nullable();
                }
                if (!Schema::hasColumn('products', 'low_stock_alert')) {
                    $table->boolean('low_stock_alert')->default(true);
                }
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
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('offline_sales');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('barcode_scans');

        // Remove added columns from existing tables
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropForeign(['promotion_id']);
                $table->dropColumn([
                    'customer_id', 'promotion_id', 'loyalty_points_used', 
                    'loyalty_points_earned', 'device_id', 'offline_sale_id'
                ]);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn([
                    'active_ingredient', 'brand', 'searchable_text', 'low_stock_alert'
                ]);
            });
        }
    }
};
