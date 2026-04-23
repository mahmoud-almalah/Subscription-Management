<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('entry_number');
            $table->enum('type', ['invoice_created', 'payment_received', 'revenue_recognized']);
            $table->ulidMorphs('reference');
            $table->string('description');
            $table->date('entry_date');
            $table->timestamps();
        });
    }
};
