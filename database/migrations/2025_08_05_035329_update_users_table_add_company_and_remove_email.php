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
            $table->dropColumn('email');
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->text('address_link')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
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
            $table->string('email')->unique()->nullable();
            $table->dropColumn([
                'company_name',
                'address',
                'address_link',
                'purchase_date',
                'expiry_date',
            ]);
        });
    }
};
