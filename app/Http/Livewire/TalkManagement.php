<?php

namespace App\Http\Livewire;

use App\Models\TalkConversation;
use App\Models\TalkMessage;
use App\Models\TalkParticipant;
use Livewire\Component;
use Livewire\WithPagination;

class TalkManagement extends Component
{
    use WithPagination;

    public $tab = 'conversations'; // conversations | messages
    public $search = '';
    public $typeFilter = '';
    public $selectedConversation = null;
    public $showModal = false;
    public $confirmDelete = null;

    // New conversation form
    public $convType = 'direct';
    public $convName = '';
    public $convDescription = '';
    public $convCreatedById = '';
    public $convCreatedByName = '';

    // Participant rows for new conversation
    public $newParticipants = [
        ['user_id' => '', 'user_name' => '', 'user_phone' => '', 'user_email' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTypeFilter() { $this->resetPage(); }

    // ── Conversation management ──────────────────────────────────────

    public function openCreate()
    {
        $this->reset(['convType','convName','convDescription','convCreatedById','convCreatedByName','newParticipants']);
        $this->convType = 'direct';
        $this->newParticipants = [['user_id'=>'','user_name'=>'','user_phone'=>'','user_email'=>'']];
        $this->showModal = true;
    }

    public function addParticipantRow()
    {
        $this->newParticipants[] = ['user_id'=>'','user_name'=>'','user_phone'=>'','user_email'=>''];
    }

    public function removeParticipantRow(int $index)
    {
        array_splice($this->newParticipants, $index, 1);
    }

    public function createConversation()
    {
        $this->validate([
            'convCreatedById'    => 'required|string',
            'convCreatedByName'  => 'required|string|max:255',
            'convType'           => 'required|in:direct,group',
            'convName'           => 'required_if:convType,group|nullable|string|max:255',
            'newParticipants.*.user_id'   => 'required|string',
            'newParticipants.*.user_name' => 'required|string|max:255',
        ], [], [
            'newParticipants.*.user_id'   => 'participant user ID',
            'newParticipants.*.user_name' => 'participant name',
        ]);

        $conversation = TalkConversation::create([
            'type'            => $this->convType,
            'name'            => $this->convName ?: null,
            'description'     => $this->convDescription ?: null,
            'created_by_id'   => $this->convCreatedById,
            'created_by_name' => $this->convCreatedByName,
            'last_message_at' => now(),
        ]);

        TalkParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $this->convCreatedById,
            'user_name'       => $this->convCreatedByName,
            'role'            => 'admin',
            'joined_at'       => now(),
        ]);

        foreach ($this->newParticipants as $p) {
            if (empty($p['user_id'])) continue;
            if ($p['user_id'] === $this->convCreatedById) continue;
            TalkParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $p['user_id'],
                'user_name'       => $p['user_name'],
                'user_phone'      => $p['user_phone'] ?: null,
                'user_email'      => $p['user_email'] ?: null,
                'role'            => 'member',
                'joined_at'       => now(),
            ]);
        }

        TalkMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->convCreatedById,
            'sender_name'     => $this->convCreatedByName,
            'type'            => 'system',
            'body'            => $this->convCreatedByName . ' created this conversation.',
        ]);

        $this->showModal = false;
        session()->flash('success', 'Conversation created.');
        $this->resetPage();
    }

    public function selectConversation(int $id)
    {
        $this->selectedConversation = TalkConversation::with(['activeParticipants'])->findOrFail($id);
        $this->tab = 'messages';
    }

    public function confirmDeleteConversation(int $id)
    {
        $this->confirmDelete = $id;
    }

    public function deleteConversation()
    {
        if ($this->confirmDelete) {
            TalkConversation::findOrFail($this->confirmDelete)->update(['is_active' => false]);
            if ($this->selectedConversation && $this->selectedConversation->id === $this->confirmDelete) {
                $this->selectedConversation = null;
                $this->tab = 'conversations';
            }
            $this->confirmDelete = null;
            session()->flash('success', 'Conversation deleted.');
        }
    }

    public function deleteMessage(int $id)
    {
        TalkMessage::findOrFail($id)->update(['is_deleted' => true, 'deleted_at' => now(), 'body' => null]);
        session()->flash('success', 'Message deleted.');
    }

    public function render()
    {
        $conversations = TalkConversation::active()
            ->with(['lastMessage', 'activeParticipants'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('created_by_name', 'like', '%'.$this->search.'%'))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->orderByDesc('last_message_at')
            ->paginate(15);

        $messages = collect();
        if ($this->selectedConversation) {
            $messages = TalkMessage::where('conversation_id', $this->selectedConversation->id)
                ->orderByDesc('created_at')
                ->paginate(30);
        }

        $stats = [
            'total_conversations' => TalkConversation::active()->count(),
            'direct'              => TalkConversation::active()->where('type', 'direct')->count(),
            'group'               => TalkConversation::active()->where('type', 'group')->count(),
            'total_messages'      => TalkMessage::where('is_deleted', false)->count(),
            'participants'        => TalkParticipant::where('is_active', true)->count(),
        ];

        return view('livewire.talk-management', compact('conversations', 'messages', 'stats'));
    }
}
