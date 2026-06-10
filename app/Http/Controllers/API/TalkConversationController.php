<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TalkConversationResource;
use App\Http\Resources\TalkMessageResource;
use App\Models\TalkConversation;
use App\Models\TalkParticipant;
use App\Models\TalkMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @group Talk – Conversations
 * WhatsApp-style messaging: manage conversations and participants.
 */
class TalkConversationController extends Controller
{
    /**
     * List Conversations
     *
     * Returns paginated conversations for a user (by user_id).
     *
     * @queryParam user_id  string required The caller's user ID. Example: user_123
     * @queryParam type     string Filter by type: direct or group. Example: group
     * @queryParam per_page integer Items per page (default 20). Example: 20
     */
    public function index(Request $request)
    {
        $request->validate(['user_id' => 'required|string']);

        $conversations = TalkConversation::active()
            ->forUser($request->user_id)
            ->with(['lastMessage', 'activeParticipants'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderByDesc('last_message_at')
            ->paginate(min($request->per_page ?? 20, 50));

        return TalkConversationResource::collection($conversations)
            ->additional(['meta' => ['user_id' => $request->user_id]]);
    }

    /**
     * Create Conversation
     *
     * Creates a direct or group conversation.
     *
     * @bodyParam type          string  required  direct or group. Example: group
     * @bodyParam name          string            Group name (required for group). Example: Family Chat
     * @bodyParam description   string            Group description.
     * @bodyParam created_by_id string  required  Creator's user ID. Example: user_123
     * @bodyParam created_by_name string required Creator's display name. Example: Alice
     * @bodyParam participants  array   required  Array of participant objects.
     * @bodyParam participants[].user_id   string required
     * @bodyParam participants[].user_name string required
     * @bodyParam participants[].user_phone string
     * @bodyParam participants[].user_email string
     * @bodyParam participants[].user_avatar string
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'              => ['required', Rule::in(['direct', 'group'])],
            'name'              => 'required_if:type,group|nullable|string|max:255',
            'description'       => 'nullable|string',
            'avatar'            => 'nullable|url',
            'created_by_id'     => 'required|string',
            'created_by_name'   => 'required|string|max:255',
            'participants'      => 'required|array|min:1',
            'participants.*.user_id'    => 'required|string',
            'participants.*.user_name'  => 'required|string|max:255',
            'participants.*.user_phone' => 'nullable|string|max:20',
            'participants.*.user_email' => 'nullable|email',
            'participants.*.user_avatar'=> 'nullable|url',
        ]);

        // For direct chats, prevent duplicate conversations between same two users
        if ($validated['type'] === 'direct') {
            $participantIds = collect($validated['participants'])->pluck('user_id')->push($validated['created_by_id'])->unique()->sort()->values();
            if ($participantIds->count() !== 2) {
                return response()->json(['message' => 'A direct conversation must have exactly 2 participants.'], 422);
            }
        }

        $conversation = TalkConversation::create([
            'type'             => $validated['type'],
            'name'             => $validated['name'] ?? null,
            'description'      => $validated['description'] ?? null,
            'avatar'           => $validated['avatar'] ?? null,
            'created_by_id'    => $validated['created_by_id'],
            'created_by_name'  => $validated['created_by_name'],
            'last_message_at'  => now(),
        ]);

        // Add creator as admin participant
        TalkParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $validated['created_by_id'],
            'user_name'       => $validated['created_by_name'],
            'role'            => 'admin',
            'joined_at'       => now(),
        ]);

        // Add other participants
        foreach ($validated['participants'] as $p) {
            if ($p['user_id'] === $validated['created_by_id']) continue; // skip if creator included
            TalkParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $p['user_id'],
                'user_name'       => $p['user_name'],
                'user_phone'      => $p['user_phone'] ?? null,
                'user_email'      => $p['user_email'] ?? null,
                'user_avatar'     => $p['user_avatar'] ?? null,
                'role'            => 'member',
                'joined_at'       => now(),
            ]);
        }

        // System message
        TalkMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $validated['created_by_id'],
            'sender_name'     => $validated['created_by_name'],
            'type'            => 'system',
            'body'            => $validated['created_by_name'] . ' created this conversation.',
        ]);

        return new TalkConversationResource($conversation->load(['activeParticipants', 'lastMessage']));
    }

    /**
     * Get Conversation
     *
     * Returns details of a conversation including participants.
     *
     * @urlParam id integer required Conversation ID. Example: 1
     */
    public function show(int $id)
    {
        $conversation = TalkConversation::with(['activeParticipants', 'lastMessage'])->findOrFail($id);
        return new TalkConversationResource($conversation);
    }

    /**
     * Delete Conversation
     *
     * Soft-deactivates a conversation.
     *
     * @urlParam id integer required Conversation ID. Example: 1
     */
    public function destroy(int $id): JsonResponse
    {
        $conversation = TalkConversation::findOrFail($id);
        $conversation->update(['is_active' => false]);
        return response()->json(['message' => 'Conversation deleted.']);
    }

    /**
     * Add Participant
     *
     * Adds a new participant to a conversation.
     *
     * @urlParam id integer required Conversation ID. Example: 1
     */
    public function addParticipant(Request $request, int $id): JsonResponse
    {
        $conversation = TalkConversation::findOrFail($id);

        $validated = $request->validate([
            'user_id'    => 'required|string',
            'user_name'  => 'required|string|max:255',
            'user_phone' => 'nullable|string|max:20',
            'user_email' => 'nullable|email',
            'user_avatar'=> 'nullable|url',
            'role'       => ['nullable', Rule::in(['admin', 'member'])],
        ]);

        $participant = TalkParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $validated['user_id']],
            array_merge($validated, ['is_active' => true, 'joined_at' => now()])
        );

        return response()->json(['message' => 'Participant added.', 'participant' => $participant]);
    }

    /**
     * Remove Participant
     *
     * Removes a participant from a conversation.
     *
     * @urlParam id     integer required Conversation ID. Example: 1
     * @urlParam userId string  required User ID to remove. Example: user_456
     */
    public function removeParticipant(int $id, string $userId): JsonResponse
    {
        TalkParticipant::where('conversation_id', $id)->where('user_id', $userId)->update(['is_active' => false]);
        return response()->json(['message' => 'Participant removed.']);
    }

    /**
     * Mark as Read
     *
     * Marks all unread messages in a conversation as read for a user.
     *
     * @urlParam  id      integer required Conversation ID. Example: 1
     * @bodyParam user_id string  required User ID. Example: user_123
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|string']);

        TalkParticipant::where('conversation_id', $id)
            ->where('user_id', $request->user_id)
            ->update(['last_read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }
}
