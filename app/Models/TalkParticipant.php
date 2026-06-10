<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalkParticipant extends Model
{
    use HasFactory;

    protected $table = 'talk_participants';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'user_name',
        'user_phone',
        'user_email',
        'user_avatar',
        'role',
        'last_read_at',
        'joined_at',
        'is_active',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at'    => 'datetime',
        'is_active'    => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(TalkConversation::class, 'conversation_id');
    }
}
