<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('gateway_id')->nullable();
            $table->string('external_id')->nullable();
            $table->string('status', 30);
            $table->integer('amount');
            $table->string('card_last_numbers', 4);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('gateway_id')->references('id')->on('gateways');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
