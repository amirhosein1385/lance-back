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
        Schema::table('profiles', function (Blueprint $table) {
            $table->integer('total_jobs_completed')->default(0)->after('rating');
            $table->integer('total_earnings')->default(0)->after('total_jobs_completed');
            $table->integer('response_time_hours')->nullable()->after('total_earnings'); // avg response time
            $table->decimal('completion_rate', 5, 2)->default(0)->after('response_time_hours'); // percentage
            $table->integer('profile_views')->default(0)->after('completion_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
