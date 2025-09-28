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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // تیتر خبر
            $table->string('slug')->unique(); // برای URL
            $table->string('cover')->nullable(); // برای URL
            $table->text('content'); // متن خبر
            $table->string('source_url')->nullable(); // لینک منبع
            $table->dateTime('published_at'); // تاریخ انتشار
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('views')->default(0); // تعداد بازدید
            $table->foreignId('news_site_id')->constrained('news_sites')->onDelete('cascade'); // ارتباط با شبکه خبری
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
