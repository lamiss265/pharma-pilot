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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('language');
            $table->text('address')->nullable()->after('phone');
            $table->string('position')->nullable()->after('address');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('position');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->json('permissions')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'position',
                'status',
                'last_login_at',
                'permissions'
            ]);
        });
    }
};
