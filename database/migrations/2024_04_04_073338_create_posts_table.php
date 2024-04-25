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
            $table->string('caption')->nullable();
            $table->string('media')->nullable();
            $table->string('tags')->nullable();
            $table->string('location', 100)->nullable();
            $table->dateTimeTz('scheduledAt')->nullable();
            $table->enum('postType', ['photo', 'video'])->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('views')->default(0);
            $table->boolean('isblocked')->default(false);
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
