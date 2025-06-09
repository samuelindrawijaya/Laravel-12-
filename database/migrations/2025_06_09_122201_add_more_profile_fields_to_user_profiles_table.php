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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->boolean('has_gerd')->nullable();
            $table->boolean('has_anxiety')->nullable();
            $table->boolean('is_on_diet')->nullable();
            $table->string('diet_type')->nullable();
            $table->string('personality_note')->nullable();
            $table->string('daily_goal_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'has_gerd',
                'has_anxiety',
                'is_on_diet',
                'diet_type',
                'personality_note',
                'daily_goal_note'
            ]);
        });
    }
};
