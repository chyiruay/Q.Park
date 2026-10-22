<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The booking form always collected a date and start time, but there was
     * nowhere to put them. Nullable so rows booked before this migration stay.
     */
    public function up(): void
    {
        Schema::table('my_carts', function (Blueprint $table) {
            $table->date('date')->nullable()->after('userID');
            $table->time('startTime')->nullable()->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('my_carts', function (Blueprint $table) {
            $table->dropColumn(['date', 'startTime']);
        });
    }
};
