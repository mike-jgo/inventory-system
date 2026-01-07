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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'gcash'])->default('cash')->after('total_amount');
            $table->decimal('amount_paid', 10, 2)->default(0.00)->after('payment_method');
            $table->decimal('change_due', 10, 2)->default(0.00)->after('amount_paid');
            $table->string('payment_reference')->nullable()->after('change_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'amount_paid', 'change_due', 'payment_reference']);
        });
    }
};
