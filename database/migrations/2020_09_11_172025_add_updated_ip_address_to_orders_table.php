<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedIpAddressToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('updated_ip_address')->nullable()->after('updated_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('updated_ip_address');
        });
    }
}
