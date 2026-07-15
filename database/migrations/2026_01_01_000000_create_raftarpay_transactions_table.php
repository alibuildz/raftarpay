<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('raftarpay.logging.table', 'raftarpay_transactions');

        Schema::create($table, function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->index();
            $table->string('reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('PKR');
            $table->string('status')->default('pending')->index();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('raftarpay.logging.table', 'raftarpay_transactions'));
    }
};
