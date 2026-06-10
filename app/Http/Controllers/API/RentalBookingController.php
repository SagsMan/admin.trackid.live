<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RentalBookingResource;
use App\Models\Rental;
use App\Models\RentalBooking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @group Rental Bookings
 * Create and manage rental bookings.
 */
class RentalBookingController extends Controller
{
    /**
     * Create Booking
     *
     * Books a rental for a specified date range.
     *
     * @bodyParam rental_id    integer required  Rental ID. Example: 1
     * @bodyParam renter_id    string            Renter's user ID (if authenticated).
     * @bodyParam renter_name  string  required  Renter's full name. Example: Jane Doe
     * @bodyParam renter_email string  required  Renter's email. Example: jane@example.com
     * @bodyParam renter_phone string            Renter's phone. Example: +2348098765432
     * @bodyParam start_date   string  required  Start date (YYYY-MM-DD). Example: 2026-07-01
     * @bodyParam end_date     string  required  End date (YYYY-MM-DD). Example: 2026-07-07
     * @bodyParam notes        string            Additional notes.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rental_id'    => 'required|exists:rentals,id',
            'renter_id'    => 'nullable|string',
            'renter_name'  => 'required|string|max:255',
            'renter_email' => 'required|email',
            'renter_phone' => 'nullable|string|max:20',
            'start_date'   => 'required|date|after_or_equal:today',
            'end_date'     => 'required|date|after:start_date',
            'notes'        => 'nullable|string',
        ]);

        $rental = Rental::findOrFail($validated['rental_id']);

        if (!$rental->is_active || $rental->status !== 'available') {
            return response()->json(['message' => 'This rental is not available for booking.'], 422);
        }

        if (!$rental->isAvailableForDates($validated['start_date'], $validated['end_date'])) {
            return response()->json(['message' => 'This rental is already booked for the selected dates.'], 422);
        }

        $start    = \Carbon\Carbon::parse($validated['start_date']);
        $end      = \Carbon\Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end);

        if ($totalDays < $rental->min_days) {
            return response()->json([
                'message' => "Minimum rental period is {$rental->min_days} day(s)."
            ], 422);
        }

        if ($rental->max_days && $totalDays > $rental->max_days) {
            return response()->json([
                'message' => "Maximum rental period is {$rental->max_days} day(s)."
            ], 422);
        }

        $booking = RentalBooking::create([
            'rental_id'    => $rental->id,
            'renter_id'    => $validated['renter_id'] ?? null,
            'renter_name'  => $validated['renter_name'],
            'renter_email' => $validated['renter_email'],
            'renter_phone' => $validated['renter_phone'] ?? null,
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'],
            'total_days'   => $totalDays,
            'price_per_day' => $rental->price_per_day,
            'total_price'  => $rental->price_per_day * $totalDays,
            'currency'     => $rental->currency,
            'notes'        => $validated['notes'] ?? null,
            'status'       => 'pending',
        ]);

        return new RentalBookingResource($booking->load('rental'));
    }

    /**
     * Get Booking
     *
     * Returns the details of a single booking.
     *
     * @urlParam id integer required Booking ID. Example: 1
     */
    public function show(int $id)
    {
        $booking = RentalBooking::with('rental.rentalCategory')->findOrFail($id);
        return new RentalBookingResource($booking);
    }

    /**
     * List Bookings for a Rental
     *
     * Returns all bookings for a specific rental.
     *
     * @urlParam rental_id integer required Rental ID. Example: 1
     * @queryParam status string Filter by status (pending, confirmed, active, completed, cancelled). Example: confirmed
     */
    public function byRental(Request $request, int $rentalId)
    {
        $rental = Rental::findOrFail($rentalId);

        $query = $rental->bookings()->with('rental');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return RentalBookingResource::collection($query->latest()->paginate(15));
    }

    /**
     * List Bookings by Renter
     *
     * Returns all bookings made by a specific renter (by renter_id or email).
     *
     * @queryParam renter_id    string Renter user ID.
     * @queryParam renter_email string Renter email.
     * @queryParam status       string Filter by status. Example: confirmed
     */
    public function byRenter(Request $request)
    {
        $request->validate([
            'renter_id'    => 'required_without:renter_email',
            'renter_email' => 'required_without:renter_id|email',
        ]);

        $query = RentalBooking::with('rental.rentalCategory');

        if ($request->renter_id) {
            $query->where('renter_id', $request->renter_id);
        } elseif ($request->renter_email) {
            $query->where('renter_email', $request->renter_email);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return RentalBookingResource::collection($query->latest()->paginate(15));
    }

    /**
     * Update Booking Status
     *
     * Updates the status of a booking. Allowed transitions:
     * - pending → confirmed or cancelled
     * - confirmed → active or cancelled
     * - active → completed or cancelled
     *
     * @urlParam  id     integer required Booking ID. Example: 1
     * @bodyParam status string  required New status. Example: confirmed
     * @bodyParam cancellation_reason string Required when cancelling. Example: Customer request
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $booking = RentalBooking::findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'active', 'completed', 'cancelled'])],
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string',
        ]);

        $allowedTransitions = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active'    => ['completed', 'cancelled'],
        ];

        if (!isset($allowedTransitions[$booking->status]) ||
            !in_array($request->status, $allowedTransitions[$booking->status])) {
            return response()->json([
                'message' => "Cannot transition from '{$booking->status}' to '{$request->status}'."
            ], 422);
        }

        $booking->status = $request->status;

        if ($request->status === 'confirmed') {
            $booking->confirmed_at = now();
        }

        if ($request->status === 'cancelled') {
            $booking->cancelled_at = now();
            $booking->cancellation_reason = $request->cancellation_reason;
        }

        $booking->save();

        return response()->json([
            'message' => 'Booking status updated.',
            'booking' => new RentalBookingResource($booking->load('rental')),
        ]);
    }
}
