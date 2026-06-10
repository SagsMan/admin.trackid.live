<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talk_conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name')->nullable();          // group name
            $table->string('avatar')->nullable();         // group avatar URL
            $table->text('description')->nullable();
            $table->string('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('talk_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('talk_conversations')->cascadeOnDelete();
            $table->string('user_id');
            $table->string('user_name');
            $table->string('user_phone')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_avatar')->nullable();
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('talk_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('talk_conversations')->cascadeOnDelete();
            $table->string('sender_id');
            $table->string('sender_name');
            $table->string('sender_avatar')->nullable();
            $table->enum('type', ['text', 'image', 'file', 'audio', 'video', 'location', 'system'])->default('text');
            $table->text('body')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('attachment_name')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable(); // bytes
            $table->json('metadata')->nullable();                       // {lat, lng, duration, etc.}
            $table->json('read_by')->nullable();                        // array of user_ids
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talk_messages');
        Schema::dropIfExists('talk_participants');
        Schema::dropIfExists('talk_conversations');
    }
};
