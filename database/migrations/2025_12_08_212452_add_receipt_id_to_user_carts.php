<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptIdToUserCarts extends Migration
{
    public function up()
    {
        Schema::table('user_carts', function (Blueprint $table) {
            $table->unsignedBigInteger('receipt_id')->nullable()->after('id');
            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('user_carts', function (Blueprint $table) {
            $table->dropForeign(['receipt_id']);
            $table->dropColumn('receipt_id');
        });
    }
}
