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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // عنوان داخلی
            $table->json('subject');
            $table->json('content')->nullable(); // موضوع
            $table->string('destination_url')->nullable(); // لینک مقصد
            $table->string('cover');
            $table->timestamp('start_date')->nullable(); // شروع نمایش
            $table->timestamp('end_date')->nullable(); // پایان نمایش
            $table->unsignedInteger('max_impressions')->nullable(); // حداکثر نمایش
            $table->unsignedInteger('max_clicks')->nullable(); // حداکثر کلیک
            $table->unsignedInteger('current_impressions')->default(0); // تعداد نمایش فعلی
            $table->unsignedInteger('current_clicks')->default(0); // تعداد کلیک فعلی
            $table->boolean('is_active')->default(true); // فعال بودن
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
