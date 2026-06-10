<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalkMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'sender_name'     => $this->sender_name,
            'sender_avatar'   => $this->sender_avatar,
            'type'            => $this->type,
            'body'            => $this->is_deleted ? null : $this->body,
            'attachment_url'  => $this->is_deleted ? null : $this->attachment_url,
            'attachment_name' => $this->attachment_name,
            'attachment_size' => $this->attachment_size,
            'metadata'        => $this->is_deleted ? null : $this->metadata,
            'read_by'         => $this->read_by ?? [],
            'is_deleted'      => $this->is_deleted,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
