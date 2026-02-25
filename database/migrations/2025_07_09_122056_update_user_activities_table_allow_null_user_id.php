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
        Schema::table('user_activities', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Change the column to allow null
            $table->foreignId('user_id')->nullable()->change();
            
            // Add the foreign key constraint back but with nullable
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_activities', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Change the column back to not null
            $table->foreignId('user_id')->nullable(false)->change();
            
            // Add the foreign key constraint back
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
