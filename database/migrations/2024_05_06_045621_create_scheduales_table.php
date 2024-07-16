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
        Schema::create('scheduales', function (Blueprint $table) {
            $table->id();
            $table->string('topic_name');
            $table->string('speaker_name');
            $table->text('summary');
            $table->text('body');
            $table->string('img');
            $table->date('date');
            $table->time('time');
            $table->text('about_speaker');
            $table->string('speaker_email');
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->foreignId('exhibition_id')->constrained('exhibitions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduales');
    }
};
