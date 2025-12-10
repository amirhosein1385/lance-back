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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade'); // profile being reviewed
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // user leaving review
            $table->integer('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->string('project_title')->nullable();
            $table->boolean('is_employer_review')->default(false); // true if reviewing an employer
            $table->boolean('is_verified_hire')->default(false); // did they actually work together?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
