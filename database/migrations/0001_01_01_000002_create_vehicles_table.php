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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->on('product_categories')->nullOnDelete();
            $table->string('name', 100)->unique();
            $table->string('type', 100)->default('');
            $table->string('plate_number')->default('');
            $table->unsignedTinyInteger('capacity')->default(0);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();

            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
