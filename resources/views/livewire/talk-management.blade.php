<div>
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-comments me-2 text-primary"></i>Talk – Messaging</h4>
            <p class="text-muted mb-0">WhatsApp-style conversations and messages</p>
        </div>
        <button class="btn btn-primary" wire:click="openCreate">
            <i class="fas fa-plus me-1"></i> New Conversation
        </button>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-comments text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['total_conversations']) }}</div>
                            <div class="text-muted small">Conversations</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-user-friends text-success fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['direct']) }}</div>
                            <div class="text-muted small">Direct Chats</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-users text-info fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['group']) }}</div>
                            <div class="text-muted small">Group Chats</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-envelope text-warning fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['total_messages']) }}</div>
                            <div class="text-muted small">Total Messages</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link @if($tab==='conversations') active @endif" wire:click="$set('tab','conversations')">
                <i class="fas fa-list me-1"></i> Conversations
            </button>
        </li>
        @if($selectedConversation)
        <li class="nav-item">
            <button class="nav-link @if($tab==='messages') active @endif" wire:click="$set('tab','messages')">
                <i class="fas fa-envelope-open me-1"></i>
                Messages
                @if($selectedConversation->type === 'group')
                    <span class="badge bg-info text-white ms-1">{{ $selectedConversation->name }}</span>
                @endif
            </button>
        </li>
        @endif
    </ul>

    {{-- CONVERSATIONS TAB --}}
    @if($tab === 'conversations')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center py-3">
            <input type="text" class="form-control form-control-sm" style="max-width:220px"
                placeholder="Search conversations..." wire:model.debounce.400ms="search">
            <select class="form-select form-select-sm" style="max-width:160px" wire:model="typeFilter">
                <option value="">All Types</option>
                <option value="direct">Direct</option>
                <option value="group">Group</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Conversation</th>
                        <th>Type</th>
                        <th>Participants</th>
                        <th>Last Message</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conv)
                    <tr>
                        <td class="text-muted small">{{ $conv->id }}</td>
                        <td>
                            <div class="fw-semibold">
                                @if($conv->type === 'group')
                                    <i class="fas fa-users text-info me-1"></i>{{ $conv->name ?? 'Group Chat' }}
                                @else
                                    <i class="fas fa-user text-success me-1"></i>Direct Chat
                                @endif
                            </div>
                            @if($conv->description)
                                <div class="text-muted small">{{ Str::limit($conv->description, 40) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $conv->type === 'group' ? 'info' : 'success' }} bg-opacity-15 text-{{ $conv->type === 'group' ? 'info' : 'success' }} text-capitalize">
                                {{ $conv->type }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                {{ $conv->activeParticipants->count() }} users
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if($conv->lastMessage)
                                @if($conv->lastMessage->is_deleted)
                                    <em>Message deleted</em>
                                @elseif($conv->lastMessage->type === 'system')
                                    <em>{{ Str::limit($conv->lastMessage->body, 35) }}</em>
                                @elseif($conv->lastMessage->type === 'text')
                                    {{ Str::limit($conv->lastMessage->body, 35) }}
                                @else
                                    <i class="fas fa-paperclip me-1"></i>{{ ucfirst($conv->lastMessage->type) }}
                                @endif
                                <div class="text-muted" style="font-size:0.7rem">{{ $conv->last_message_at?->diffForHumans() }}</div>
                            @else
                                <span class="text-muted">No messages</span>
                            @endif
                        </td>
                        <td class="small">{{ $conv->created_by_name }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1"
                                wire:click="selectConversation({{ $conv->id }})"
                                title="View Messages">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                wire:click="confirmDeleteConversation({{ $conv->id }})"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No conversations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $conversations->links() }}</div>
    </div>
    @endif

    {{-- MESSAGES TAB --}}
    @if($tab === 'messages' && $selectedConversation)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex align-items-center gap-3 py-3">
            <button class="btn btn-sm btn-outline-secondary" wire:click="$set('tab','conversations')">
                <i class="fas fa-arrow-left me-1"></i> Back
            </button>
            <div>
                <div class="fw-semibold">
                    @if($selectedConversation->type === 'group')
                        <i class="fas fa-users text-info me-1"></i>{{ $selectedConversation->name ?? 'Group Chat' }}
                    @else
                        <i class="fas fa-user text-success me-1"></i>Direct Conversation
                    @endif
                </div>
                <div class="text-muted small">
                    {{ $selectedConversation->activeParticipants->count() }} participant(s):
                    {{ $selectedConversation->activeParticipants->pluck('user_name')->join(', ') }}
                </div>
            </div>
        </div>

        {{-- Participants summary --}}
        <div class="px-3 pt-2 pb-1 border-bottom bg-light">
            <div class="d-flex flex-wrap gap-1">
                @foreach($selectedConversation->activeParticipants as $p)
                <span class="badge bg-{{ $p->role === 'admin' ? 'primary' : 'secondary' }} bg-opacity-10 text-{{ $p->role === 'admin' ? 'primary' : 'secondary' }}">
                    <i class="fas fa-{{ $p->role === 'admin' ? 'crown' : 'user' }} me-1 fa-xs"></i>{{ $p->user_name }}
                    @if($p->user_phone) <span class="ms-1 opacity-75">{{ $p->user_phone }}</span>@endif
                </span>
                @endforeach
            </div>
        </div>

        {{-- Messages list --}}
        <div class="p-3" style="background:#f0f0f0; min-height: 400px; max-height: 600px; overflow-y: auto;">
            @forelse($messages->reverse() as $msg)
            <div class="d-flex mb-3 @if($msg->type === 'system') justify-content-center @endif">
                @if($msg->type === 'system')
                    <div class="badge bg-white text-muted shadow-sm px-3 py-1 rounded-pill small">
                        {{ $msg->body }}
                    </div>
                @else
                <div style="max-width: 70%;">
                    <div class="bg-white rounded-3 p-2 shadow-sm position-relative">
                        <div class="fw-semibold text-primary small mb-1">{{ $msg->sender_name }}</div>
                        @if($msg->is_deleted)
                            <em class="text-muted small"><i class="fas fa-ban me-1"></i>This message was deleted</em>
                        @elseif($msg->type === 'text')
                            <div>{{ $msg->body }}</div>
                        @elseif(in_array($msg->type, ['image']))
                            @if($msg->attachment_url)
                                <img src="{{ $msg->attachment_url }}" class="img-fluid rounded" style="max-width:200px">
                            @endif
                        @elseif(in_array($msg->type, ['file', 'audio', 'video']))
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-{{ $msg->type === 'audio' ? 'microphone' : ($msg->type === 'video' ? 'video' : 'file') }} text-primary fa-lg"></i>
                                <div>
                                    <div class="small fw-semibold">{{ $msg->attachment_name ?? ucfirst($msg->type) }}</div>
                                    @if($msg->attachment_size)
                                        <div class="text-muted" style="font-size:0.7rem">{{ number_format($msg->attachment_size / 1024, 1) }} KB</div>
                                    @endif
                                </div>
                                @if($msg->attachment_url)
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </div>
                        @elseif($msg->type === 'location')
                            <div class="small">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                @if($msg->metadata && isset($msg->metadata['lat']))
                                    {{ $msg->metadata['lat'] }}, {{ $msg->metadata['lng'] }}
                                @else
                                    Location shared
                                @endif
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted" style="font-size:0.65rem">{{ $msg->created_at->format('d M H:i') }}</span>
                            <div class="d-flex align-items-center gap-1">
                                @if(!empty($msg->read_by) && count($msg->read_by) > 1)
                                    <i class="fas fa-check-double text-primary fa-xs" title="Read by {{ count($msg->read_by) }}"></i>
                                @endif
                                @if(!$msg->is_deleted)
                                    <button class="btn btn-link btn-sm p-0 ms-2 text-danger" wire:click="deleteMessage({{ $msg->id }})"
                                        style="font-size:0.65rem" title="Delete message">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-comments fa-3x mb-3 opacity-25"></i><br>
                No messages yet.
            </div>
            @endforelse
        </div>
        <div class="card-footer bg-white">{{ $messages->links() }}</div>
    </div>
    @endif

    {{-- Create Conversation Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>New Conversation</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Type --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type *</label>
                            <select class="form-select" wire:model="convType">
                                <option value="direct">Direct (1-on-1)</option>
                                <option value="group">Group Chat</option>
                            </select>
                        </div>
                        @if($convType === 'group')
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Group Name *</label>
                            <input type="text" class="form-control @error('convName') is-invalid @enderror"
                                wire:model="convName" placeholder="e.g. Family Group">
                            @error('convName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Group Description</label>
                            <textarea class="form-control" rows="2" wire:model="convDescription" placeholder="Optional description..."></textarea>
                        </div>
                        @endif

                        {{-- Creator --}}
                        <div class="col-12"><hr class="my-1"><p class="fw-semibold mb-0 text-muted small text-uppercase">Creator / Admin</p></div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Creator User ID *</label>
                            <input type="text" class="form-control @error('convCreatedById') is-invalid @enderror"
                                wire:model="convCreatedById" placeholder="e.g. user_123">
                            @error('convCreatedById') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Creator Name *</label>
                            <input type="text" class="form-control @error('convCreatedByName') is-invalid @enderror"
                                wire:model="convCreatedByName" placeholder="e.g. Alice">
                            @error('convCreatedByName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Participants --}}
                        <div class="col-12">
                            <hr class="my-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="fw-semibold mb-0 text-muted small text-uppercase">Participants</p>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addParticipantRow">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                            </div>
                            @foreach($newParticipants as $i => $p)
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm @error('newParticipants.'.$i.'.user_id') is-invalid @enderror"
                                        wire:model="newParticipants.{{ $i }}.user_id" placeholder="User ID *">
                                    @error('newParticipants.'.$i.'.user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm @error('newParticipants.'.$i.'.user_name') is-invalid @enderror"
                                        wire:model="newParticipants.{{ $i }}.user_name" placeholder="Name *">
                                    @error('newParticipants.'.$i.'.user_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="newParticipants.{{ $i }}.user_phone" placeholder="Phone">
                                </div>
                                <div class="col">
                                    <input type="email" class="form-control form-control-sm"
                                        wire:model="newParticipants.{{ $i }}.user_email" placeholder="Email">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeParticipantRow({{ $i }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showModal',false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="createConversation" wire:loading.attr="disabled">
                        <span wire:loading wire:target="createConversation" class="spinner-border spinner-border-sm me-1"></span>
                        Create Conversation
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if($confirmDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Conversation</h5>
                    <button type="button" class="btn-close" wire:click="$set('confirmDelete',null)"></button>
                </div>
                <div class="modal-body">Are you sure? This will deactivate the conversation and hide it from all users.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('confirmDelete',null)">Cancel</button>
                    <button class="btn btn-danger" wire:click="deleteConversation">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
