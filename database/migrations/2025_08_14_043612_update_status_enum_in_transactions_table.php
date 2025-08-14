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
        Schema::table('transactions', function (Blueprint $table) {
            \DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('free', 'pending', 'completed', 'admin_credit') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            \DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('free', 'pending', 'completed') DEFAULT 'pending'");
        });
    }
};
