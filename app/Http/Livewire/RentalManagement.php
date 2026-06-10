<?php

namespace App\Http\Livewire;

use App\Models\Rental;
use App\Models\RentalBooking;
use App\Models\RentalCategory;
use Livewire\Component;
use Livewire\WithPagination;

class RentalManagement extends Component
{
    use WithPagination;

    public $tab = 'listings'; // listings | bookings | categories
    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $showModal = false;
    public $editingRental = null;
    public $confirmDelete = null;

    // Form fields
    public $title = '';
    public $description = '';
    public $category = 'vehicle';
    public $rental_category_id = '';
    public $price_per_day = '';
    public $currency = 'USD';
    public $location = '';
    public $owner_name = '';
    public $owner_phone = '';
    public $owner_email = '';
    public $status = 'available';
    public $is_active = true;
    public $min_days = 1;
    public $max_days = '';
    public $terms = '';

    protected $rules = [
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'category'    => 'required|in:vehicle,property,equipment,electronics,clothing,other',
        'price_per_day' => 'required|numeric|min:0',
        'currency'    => 'required|string|max:10',
        'location'    => 'required|string|max:255',
        'owner_name'  => 'required|string|max:255',
        'owner_phone' => 'nullable|string|max:20',
        'owner_email' => 'nullable|email',
        'status'      => 'required|in:available,unavailable,maintenance',
        'is_active'   => 'boolean',
        'min_days'    => 'integer|min:1',
        'max_days'    => 'nullable|integer|min:1',
        'terms'       => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['title','description','category','price_per_day','currency','location','owner_name','owner_phone','owner_email','status','is_active','min_days','max_days','terms','editingRental']);
        $this->currency   = 'USD';
        $this->status     = 'available';
        $this->is_active  = true;
        $this->min_days   = 1;
        $this->category   = 'vehicle';
        $this->showModal  = true;
    }

    public function openEdit(int $id)
    {
        $rental = Rental::findOrFail($id);
        $this->editingRental   = $id;
        $this->title           = $rental->title;
        $this->description     = $rental->description;
        $this->category        = $rental->category;
        $this->rental_category_id = $rental->rental_category_id;
        $this->price_per_day   = $rental->price_per_day;
        $this->currency        = $rental->currency;
        $this->location        = $rental->location;
        $this->owner_name      = $rental->owner_name;
        $this->owner_phone     = $rental->owner_phone;
        $this->owner_email     = $rental->owner_email;
        $this->status          = $rental->status;
        $this->is_active       = $rental->is_active;
        $this->min_days        = $rental->min_days;
        $this->max_days        = $rental->max_days;
        $this->terms           = $rental->terms;
        $this->showModal       = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title'              => $this->title,
            'description'        => $this->description,
            'category'           => $this->category,
            'rental_category_id' => $this->rental_category_id ?: null,
            'price_per_day'      => $this->price_per_day,
            'currency'           => $this->currency,
            'location'           => $this->location,
            'owner_name'         => $this->owner_name,
            'owner_phone'        => $this->owner_phone,
            'owner_email'        => $this->owner_email,
            'status'             => $this->status,
            'is_active'          => $this->is_active,
            'min_days'           => $this->min_days,
            'max_days'           => $this->max_days ?: null,
            'terms'              => $this->terms,
        ];

        if ($this->editingRental) {
            Rental::findOrFail($this->editingRental)->update($data);
            session()->flash('success', 'Rental updated successfully.');
        } else {
            Rental::create($data);
            session()->flash('success', 'Rental created successfully.');
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function confirmDeleteRental(int $id)
    {
        $this->confirmDelete = $id;
    }

    public function deleteRental()
    {
        if ($this->confirmDelete) {
            Rental::findOrFail($this->confirmDelete)->delete();
            $this->confirmDelete = null;
            session()->flash('success', 'Rental deleted.');
        }
    }

    public function updateBookingStatus(int $bookingId, string $status)
    {
        $booking = RentalBooking::findOrFail($bookingId);
        $booking->status = $status;
        if ($status === 'confirmed') $booking->confirmed_at = now();
        if ($status === 'cancelled') $booking->cancelled_at = now();
        $booking->save();
        session()->flash('success', 'Booking status updated.');
    }

    public function render()
    {
        $rentals = Rental::with('rentalCategory')
            ->when($this->search, fn($q) => $q->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('location', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->latest()->paginate(10);

        $bookings = RentalBooking::with('rental')
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()->paginate(10);

        $categories = RentalCategory::withCount('rentals')->inorder()->get();

        $stats = [
            'total'       => Rental::count(),
            'available'   => Rental::where('status', 'available')->count(),
            'bookings'    => RentalBooking::count(),
            'pending'     => RentalBooking::where('status', 'pending')->count(),
            'revenue'     => RentalBooking::whereIn('status', ['confirmed','active','completed'])->sum('total_price'),
        ];

        return view('livewire.rental-management', compact('rentals', 'bookings', 'categories', 'stats'));
    }
}
