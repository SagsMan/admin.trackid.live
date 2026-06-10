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
            <h4 class="mb-1 fw-bold">Rental Management</h4>
            <p class="text-muted mb-0">Manage listings, bookings and categories</p>
        </div>
        @if($tab === 'listings')
            <button class="btn btn-primary" wire:click="openCreate">
                <i class="fas fa-plus me-1"></i> Add Rental
            </button>
        @endif
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded p-2 me-3">
                            <i class="fas fa-list text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['total']) }}</div>
                            <div class="text-muted small">Total Listings</div>
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
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['available']) }}</div>
                            <div class="text-muted small">Available</div>
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
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['pending']) }}</div>
                            <div class="text-muted small">Pending Bookings</div>
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
                            <i class="fas fa-dollar-sign text-info fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($stats['revenue'], 2) }}</div>
                            <div class="text-muted small">Total Revenue</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link @if($tab==='listings') active @endif" wire:click="$set('tab','listings')">
                <i class="fas fa-home me-1"></i> Listings
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link @if($tab==='bookings') active @endif" wire:click="$set('tab','bookings')">
                <i class="fas fa-calendar-check me-1"></i> Bookings
                @if($stats['pending'] > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $stats['pending'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link @if($tab==='categories') active @endif" wire:click="$set('tab','categories')">
                <i class="fas fa-tags me-1"></i> Categories
            </button>
        </li>
    </ul>

    {{-- LISTINGS TAB --}}
    @if($tab === 'listings')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center py-3">
            <input type="text" class="form-control form-control-sm" style="max-width:220px"
                placeholder="Search listings..." wire:model.debounce.400ms="search">
            <select class="form-select form-select-sm" style="max-width:160px" wire:model="statusFilter">
                <option value="">All Statuses</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <select class="form-select form-select-sm" style="max-width:160px" wire:model="categoryFilter">
                <option value="">All Categories</option>
                <option value="vehicle">Vehicle</option>
                <option value="property">Property</option>
                <option value="equipment">Equipment</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price/Day</th>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rentals as $rental)
                    <tr>
                        <td class="text-muted small">{{ $rental->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($rental->title, 30) }}</div>
                            <div class="text-muted small">Min {{ $rental->min_days }} day(s)</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary text-capitalize">
                                {{ $rental->category }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $rental->currency }} {{ number_format($rental->price_per_day, 2) }}</td>
                        <td class="text-muted small">{{ Str::limit($rental->location, 25) }}</td>
                        <td>
                            <div class="small">{{ $rental->owner_name }}</div>
                            @if($rental->owner_phone)
                                <div class="text-muted small">{{ $rental->owner_phone }}</div>
                            @endif
                        </td>
                        <td>
                            @php $sc = ['available'=>'success','unavailable'=>'secondary','maintenance'=>'warning']; @endphp
                            <span class="badge bg-{{ $sc[$rental->status] ?? 'secondary' }} bg-opacity-15 text-{{ $sc[$rental->status] ?? 'secondary' }} text-capitalize">
                                {{ $rental->status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $rental->is_active ? 'success' : 'danger' }}">
                                {{ $rental->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" wire:click="openEdit({{ $rental->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" wire:click="confirmDeleteRental({{ $rental->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No rentals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $rentals->links() }}</div>
    </div>
    @endif

    {{-- BOOKINGS TAB --}}
    @if($tab === 'bookings')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex gap-2 align-items-center py-3">
            <select class="form-select form-select-sm" style="max-width:180px" wire:model="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Rental</th>
                        <th>Renter</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td class="text-muted small">{{ $booking->id }}</td>
                        <td>
                            <div class="small fw-semibold">{{ Str::limit($booking->rental?->title ?? 'N/A', 25) }}</div>
                        </td>
                        <td>
                            <div class="small fw-semibold">{{ $booking->renter_name }}</div>
                            <div class="text-muted small">{{ $booking->renter_email }}</div>
                        </td>
                        <td class="small">
                            {{ $booking->start_date?->format('d M Y') }} →
                            {{ $booking->end_date?->format('d M Y') }}
                        </td>
                        <td>{{ $booking->total_days }}</td>
                        <td class="fw-semibold">{{ $booking->currency }} {{ number_format($booking->total_price, 2) }}</td>
                        <td>
                            @php $sc=['pending'=>'warning','confirmed'=>'info','active'=>'primary','completed'=>'success','cancelled'=>'danger']; @endphp
                            <span class="badge bg-{{ $sc[$booking->status] ?? 'secondary' }} text-capitalize">
                                {{ $booking->status }}
                            </span>
                        </td>
                        <td>
                            @if($booking->status === 'pending')
                                <button class="btn btn-xs btn-success me-1" wire:click="updateBookingStatus({{ $booking->id }},'confirmed')">Confirm</button>
                                <button class="btn btn-xs btn-danger" wire:click="updateBookingStatus({{ $booking->id }},'cancelled')">Cancel</button>
                            @elseif($booking->status === 'confirmed')
                                <button class="btn btn-xs btn-primary me-1" wire:click="updateBookingStatus({{ $booking->id }},'active')">Activate</button>
                                <button class="btn btn-xs btn-danger" wire:click="updateBookingStatus({{ $booking->id }},'cancelled')">Cancel</button>
                            @elseif($booking->status === 'active')
                                <button class="btn btn-xs btn-success" wire:click="updateBookingStatus({{ $booking->id }},'completed')">Complete</button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $bookings->links() }}</div>
    </div>
    @endif

    {{-- CATEGORIES TAB --}}
    @if($tab === 'categories')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Rentals</th>
                        <th>Active</th>
                        <th>Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="text-muted small">{{ $cat->id }}</td>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>{{ $cat->rentals_count }}</td>
                        <td>
                            <span class="badge bg-{{ $cat->is_active ? 'success' : 'danger' }}">
                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $cat->order }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingRental ? 'Edit Rental' : 'Add New Rental' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="e.g. Toyota Corolla 2022">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category *</label>
                            <select class="form-select @error('category') is-invalid @enderror" wire:model="category">
                                <option value="vehicle">Vehicle</option>
                                <option value="property">Property</option>
                                <option value="equipment">Equipment</option>
                                <option value="electronics">Electronics</option>
                                <option value="clothing">Clothing</option>
                                <option value="other">Other</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status *</label>
                            <select class="form-select" wire:model="status">
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price Per Day *</label>
                            <input type="number" step="0.01" class="form-control @error('price_per_day') is-invalid @enderror" wire:model="price_per_day">
                            @error('price_per_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Currency</label>
                            <input type="text" class="form-control" wire:model="currency" placeholder="USD">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Min Days</label>
                            <input type="number" class="form-control" wire:model="min_days" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Max Days</label>
                            <input type="number" class="form-control" wire:model="max_days" min="1" placeholder="—">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Location *</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" wire:model="location" placeholder="e.g. Lagos, Nigeria">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Owner Name *</label>
                            <input type="text" class="form-control @error('owner_name') is-invalid @enderror" wire:model="owner_name">
                            @error('owner_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Owner Phone</label>
                            <input type="text" class="form-control" wire:model="owner_phone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Owner Email</label>
                            <input type="email" class="form-control" wire:model="owner_email">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Terms & Conditions</label>
                            <textarea class="form-control" rows="2" wire:model="terms"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveCheck">
                                <label class="form-check-label" for="isActiveCheck">Active (visible to users)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('showModal',false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $editingRental ? 'Update Rental' : 'Create Rental' }}
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
                    <h5 class="modal-title text-danger">Delete Rental</h5>
                    <button type="button" class="btn-close" wire:click="$set('confirmDelete',null)"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this rental? This action cannot be undone.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('confirmDelete',null)">Cancel</button>
                    <button class="btn btn-danger" wire:click="deleteRental">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
