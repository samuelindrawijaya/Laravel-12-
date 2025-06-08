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
            $table->string('gender')->nullable()->after('bio'); // male, female, other
            $table->date('birthdate')->nullable()->after('gender');
            $table->string('instagram')->nullable()->after('birthdate');
            $table->string('linkedin')->nullable()->after('instagram');
            $table->string('github')->nullable()->after('linkedin');
            $table->string('website')->nullable()->after('github');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'birthdate', 'instagram', 'linkedin', 'github', 'website'
            ]);
        });
    }
};
