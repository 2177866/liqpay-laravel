<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liqpay_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id')->unique();
            $table->enum('status', ['active', 'inactive', 'canceled'])->default('active');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5);
            $table->string('description')->nullable();
            $table->string('liqpay_order_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->json('info')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->unsignedBigInteger('last_payment_id')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('liqpay_data')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liqpay_subscriptions');
    }
};
