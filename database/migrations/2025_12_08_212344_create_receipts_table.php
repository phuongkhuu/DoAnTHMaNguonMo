<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id(); // numeric receipt_id
            $table->uuid('uuid')->unique(); // stable external id if needed
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable(); // for guests
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
