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
            $table->string('code', 20)->unique();
            $table->string('description', 100)->nullable()->default('');
            $table->string('type', 20)->nullable()->default('');
            $table->string('plate_number')->nullable()->default('');
            $table->unsignedTinyInteger('capacity')->default(0);
            $table->string('status', 20)->nullable()->default('');
            $table->string('brand', 40)->nullable()->default('');
            $table->string('model', 40)->nullable()->default('');
            $table->unsignedInteger('year')->nullable();
            $table->text('notes')->nullable();

            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index('type');
            $table->index('plate_number');
            $table->index('status');
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
