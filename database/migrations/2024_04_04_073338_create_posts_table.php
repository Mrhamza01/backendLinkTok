<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('caption')->nullable(); // Caption can be nullable if only media is provided
            $table->string('media')->nullable(); // Media can be nullable if only content is provided
            $table->string('tags')->nullable(); // Tags can be nullable and should not have a default 'null' string
            $table->string('location', 100)->nullable(); // Location can be nullable
            $table->dateTimeTz('scheduledAt')->nullable(); // Scheduled time can be nullable for immediate posts
            $table->enum('postType', ['photo', 'video'])->nullable(); // Post type can be nullable if no media is provided
            $table->boolean('is_scheduled')->default(false); // Add a flag to indicate if the post is scheduled
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('impressions')->default(0);
            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
