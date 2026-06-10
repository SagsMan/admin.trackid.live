<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TalkMessageResource;
use App\Models\TalkConversation;
use App\Models\TalkMessage;
use App\Models\TalkParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @group Talk – Messages
 * Send and retrieve messages within a conversation.
 */
class TalkMessageController extends Controller
{
    /**
     * List Messages
     *
     * Returns paginated messages for a conversation (newest last).
     *
     * @urlParam  id       integer required Conversation ID. Example: 1
     * @queryParam per_page integer Items per page (default 30). Example: 30
     * @queryParam before   integer Message ID — return messages before this ID (for pagination). Example: 100
     */
    public function index(Request $request, int $conversationId)
    {
        $conversation = TalkConversation::findOrFail($conversationId);

        $query = $conversation->messages()
            ->where('is_deleted', false)
            ->orderByDesc('id');

        if ($request->before) {
            $query->where('id', '<', $request->before);
        }

        $perPage = min($request->per_page ?? 30, 100);
        $messages = $query->paginate($perPage);

        // Return in ascending order (oldest first, like WhatsApp)
        $messages->getCollection()->transform(fn($m) => $m);

        return TalkMessageResource::collection($messages);
    }

    /**
     * Send Message
     *
     * Sends a new message to a conversation.
     *
     * @urlParam id integer required Conversation ID. Example: 1
     * @bodyParam sender_id     string required Sender's user ID. Example: user_123
     * @bodyParam sender_name   string required Sender's display name. Example: Alice
     * @bodyParam sender_avatar string          Sender's avatar URL.
     * @bodyParam type          string          Message type: text|image|file|audio|video|location. Default: text.
     * @bodyParam body          string          Message text body (required for type=text).
     * @bodyParam attachment_url  string        URL of attachment (required for image/file/audio/video).
     * @bodyParam attachment_name string        Original filename.
     * @bodyParam attachment_size integer       File size in bytes.
     * @bodyParam metadata      object          Extra data (e.g. {lat, lng} for location).
     */
    public function store(Request $request, int $conversationId)
    {
        $conversation = TalkConversation::findOrFail($conversationId);

        $validated = $request->validate([
            'sender_id'       => 'required|string',
            'sender_name'     => 'required|string|max:255',
            'sender_avatar'   => 'nullable|url',
            'type'            => ['nullable', Rule::in(['text', 'image', 'file', 'audio', 'video', 'location', 'system'])],
            'body'            => 'required_if:type,text|nullable|string',
            'attachment_url'  => 'required_if:type,image|required_if:type,file|required_if:type,audio|required_if:type,video|nullable|url',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_size' => 'nullable|integer|min:0',
            'metadata'        => 'nullable|array',
        ]);

        $message = TalkMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $validated['sender_id'],
            'sender_name'     => $validated['sender_name'],
            'sender_avatar'   => $validated['sender_avatar'] ?? null,
            'type'            => $validated['type'] ?? 'text',
            'body'            => $validated['body'] ?? null,
            'attachment_url'  => $validated['attachment_url'] ?? null,
            'attachment_name' => $validated['attachment_name'] ?? null,
            'attachment_size' => $validated['attachment_size'] ?? null,
            'metadata'        => $validated['metadata'] ?? null,
            'read_by'         => [$validated['sender_id']], // sender has read their own message
        ]);

        // Update conversation last_message_at
        $conversation->update([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
        ]);

        return new TalkMessageResource($message);
    }

    /**
     * Delete Message
     *
     * Soft-deletes a message (hides content, shows "This message was deleted").
     *
     * @urlParam id integer required Message ID. Example: 5
     */
    public function destroy(int $id): JsonResponse
    {
        $message = TalkMessage::findOrFail($id);
        $message->update(['is_deleted' => true, 'deleted_at' => now(), 'body' => null]);
        return response()->json(['message' => 'Message deleted.']);
    }
}
