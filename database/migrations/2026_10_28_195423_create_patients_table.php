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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('university_id')->unique();
            $table->date('dob');
            $table->string('gender');
            $table->string('blood_group')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('role_category')->default('student');
            $table->string('department')->nullable();
            $table->string('year_semester')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('allergies')->nullable();
            $table->json('conditions')->nullable();
            $table->text('medications')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
