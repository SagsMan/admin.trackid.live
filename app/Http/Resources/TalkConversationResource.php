<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalkConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'name'             => $this->name,
            'avatar'           => $this->avatar,
            'description'      => $this->description,
            'created_by_id'    => $this->created_by_id,
            'created_by_name'  => $this->created_by_name,
            'last_message_at'  => $this->last_message_at?->toISOString(),
            'is_active'        => $this->is_active,
            'participants'     => $this->whenLoaded('activeParticipants', fn() =>
                $this->activeParticipants->map(fn($p) => [
                    'id'           => $p->id,
                    'user_id'      => $p->user_id,
                    'user_name'    => $p->user_name,
                    'user_phone'   => $p->user_phone,
                    'user_email'   => $p->user_email,
                    'user_avatar'  => $p->user_avatar,
                    'role'         => $p->role,
                    'last_read_at' => $p->last_read_at?->toISOString(),
                    'joined_at'    => $p->joined_at?->toISOString(),
                ])
            ),
            'last_message'     => $this->whenLoaded('lastMessage', fn() =>
                $this->lastMessage ? [
                    'id'          => $this->lastMessage->id,
                    'type'        => $this->lastMessage->type,
                    'body'        => $this->lastMessage->is_deleted ? 'This message was deleted' : $this->lastMessage->body,
                    'sender_id'   => $this->lastMessage->sender_id,
                    'sender_name' => $this->lastMessage->sender_name,
                    'created_at'  => $this->lastMessage->created_at?->toISOString(),
                    'is_deleted'  => $this->lastMessage->is_deleted,
                ] : null
            ),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
