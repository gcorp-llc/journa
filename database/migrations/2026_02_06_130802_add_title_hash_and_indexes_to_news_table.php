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
        Schema::table('news', function (Blueprint $table) {
            // افزودن هش عنوان برای جلوگیری از تکرار
            $table->string('title_hash')->nullable()->after('title');

            // افزودن ایندکس‌ها برای پرفورمنس
            $table->index('status');
            $table->index('published_at');
            $table->index('news_site_id');
            $table->index('source_url');
            $table->unique('title_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropUnique(['title_hash']);
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['news_site_id']);
            $table->dropIndex(['source_url']);
            $table->dropColumn('title_hash');
        });
    }
};
