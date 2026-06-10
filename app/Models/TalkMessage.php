<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalkMessage extends Model
{
    use HasFactory;

    protected $table = 'talk_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_name',
        'sender_avatar',
        'type',
        'body',
        'attachment_url',
        'attachment_name',
        'attachment_size',
        'metadata',
        'read_by',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'read_by'    => 'array',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(TalkConversation::class, 'conversation_id');
    }

    public function markReadBy(string $userId): void
    {
        $readBy = $this->read_by ?? [];
        if (!in_array($userId, $readBy)) {
            $readBy[] = $userId;
            $this->update(['read_by' => $readBy]);
        }
    }

    public function isReadBy(string $userId): bool
    {
        return in_array($userId, $this->read_by ?? []);
    }
}
