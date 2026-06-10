<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalkConversation extends Model
{
    use HasFactory;

    protected $table = 'talk_conversations';

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'description',
        'created_by_id',
        'created_by_name',
        'last_message_id',
        'last_message_at',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata'        => 'array',
        'is_active'       => 'boolean',
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function participants()
    {
        return $this->hasMany(TalkParticipant::class, 'conversation_id');
    }

    public function activeParticipants()
    {
        return $this->hasMany(TalkParticipant::class, 'conversation_id')->where('is_active', true);
    }

    public function messages()
    {
        return $this->hasMany(TalkMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(TalkMessage::class, 'conversation_id')->latestOfMany();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->whereHas('participants', fn($q) => $q->where('user_id', $userId)->where('is_active', true));
    }

    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    // Helpers
    public function getUnreadCountFor(string $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant) return 0;

        $query = $this->messages()->where('is_deleted', false)->where('sender_id', '!=', $userId);
        if ($participant->last_read_at) {
            $query->where('created_at', '>', $participant->last_read_at);
        }
        return $query->count();
    }
}
