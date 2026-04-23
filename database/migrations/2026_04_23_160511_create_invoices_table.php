<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('invoice_number');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('revenue_recognized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
        });
    }
};
