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
        // First, convert existing decimal price values to paisa (multiply by 100)
        \DB::statement('UPDATE courses SET price = ROUND(price * 100) WHERE price IS NOT NULL');

        Schema::table('courses', function (Blueprint $table) {
            // Change price column from decimal to unsigned integer (paisa)
            $table->unsignedInteger('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Change back to decimal and convert paisa back to rupees
            $table->decimal('price', 10, 2)->nullable()->change();
        });

        // Convert paisa back to rupees (divide by 100)
        \DB::statement('UPDATE courses SET price = price / 100 WHERE price IS NOT NULL');
    }
};
