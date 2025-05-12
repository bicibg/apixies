<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('details')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_email')->nullable();
            $table->enum('status', ['pending', 'planned', 'rejected', 'done'])->default('pending');
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();
        });

        Schema::create('suggestion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('anon_hash', 64)->nullable()->index();   // hashed IP+UA for guests
            $table->timestamps();
            $table->unique(['suggestion_id', 'user_id']);
            $table->unique(['suggestion_id', 'anon_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_votes');
        Schema::dropIfExists('suggestions');
    }
};
