<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Models\RentalCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @group Rentals
 * Manage rental listings — browse, create, update, delete, and check availability.
 */
class RentalController extends Controller
{
    /**
     * List Rentals
     *
     * Returns a paginated list of active rental listings.
     *
     * @queryParam category string Filter by category slug (vehicle, property, equipment, electronics, other). Example: vehicle
     * @queryParam rental_category_id integer Filter by rental category ID. Example: 1
     * @queryParam location string Filter by location keyword. Example: Lagos
     * @queryParam min_price number Minimum price per day. Example: 10
     * @queryParam max_price number Maximum price per day. Example: 500
     * @queryParam latitude  number Latitude for nearby search. Example: 6.5244
     * @queryParam longitude number Longitude for nearby search. Example: 3.3792
     * @queryParam radius    number Radius in km for nearby search (default 50). Example: 20
     * @queryParam per_page  integer Items per page (default 15). Example: 20
     * @queryParam page      integer Page number. Example: 1
     */
    public function index(Request $request)
    {
        $query = Rental::with('rentalCategory')
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->rental_category_id) {
            $query->where('rental_category_id', $request->rental_category_id);
        }

        if ($request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->min_price) {
            $query->where('price_per_day', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price_per_day', '<=', $request->max_price);
        }

        if ($request->latitude && $request->longitude) {
            $radius = $request->radius ?? 50;
            $query->nearby($request->latitude, $request->longitude, $radius);
        } else {
            $query->latest();
        }

        $perPage = min($request->per_page ?? 15, 50);
        return RentalResource::collection($query->paginate($perPage));
    }

    /**
     * Get Rental
     *
     * Returns details of a single rental listing.
     *
     * @urlParam id integer required The rental ID. Example: 1
     */
    public function show(int $id)
    {
        $rental = Rental::with('rentalCategory')->findOrFail($id);
        return new RentalResource($rental);
    }

    /**
     * Create Rental
     *
     * Creates a new rental listing.
     *
     * @bodyParam title        string  required  Title of the rental. Example: Toyota Corolla 2020
     * @bodyParam description  string  required  Description. Example: Clean and well-maintained car.
     * @bodyParam category     string  required  Category type (vehicle, property, equipment, electronics, other). Example: vehicle
     * @bodyParam price_per_day number required  Price per day. Example: 50.00
     * @bodyParam currency     string            Currency code (default USD). Example: NGN
     * @bodyParam location     string  required  Location of the rental. Example: Lagos, Nigeria
     * @bodyParam latitude     number            GPS latitude. Example: 6.5244
     * @bodyParam longitude    number            GPS longitude. Example: 3.3792
     * @bodyParam images       array             Array of image URLs.
     * @bodyParam owner_id     string            Owner user ID.
     * @bodyParam owner_name   string  required  Owner full name. Example: John Doe
     * @bodyParam owner_phone  string            Owner phone number. Example: +2348012345678
     * @bodyParam owner_email  string            Owner email. Example: john@example.com
     * @bodyParam min_days     integer           Minimum rental days (default 1). Example: 1
     * @bodyParam max_days     integer           Maximum rental days. Example: 30
     * @bodyParam terms        string            Rental terms and conditions.
     * @bodyParam features     array             List of features/amenities.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'category'           => ['required', Rule::in(['vehicle', 'property', 'equipment', 'electronics', 'clothing', 'other'])],
            'rental_category_id' => 'nullable|exists:rental_categories,id',
            'price_per_day'      => 'required|numeric|min:0',
            'currency'           => 'nullable|string|max:10',
            'location'           => 'required|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'images'             => 'nullable|array',
            'images.*'           => 'nullable|url',
            'owner_id'           => 'nullable|string',
            'owner_name'         => 'required|string|max:255',
            'owner_phone'        => 'nullable|string|max:20',
            'owner_email'        => 'nullable|email',
            'min_days'           => 'nullable|integer|min:1',
            'max_days'           => 'nullable|integer|min:1',
            'terms'              => 'nullable|string',
            'features'           => 'nullable|array',
        ]);

        $rental = Rental::create($validated);
        return new RentalResource($rental->load('rentalCategory'));
    }

    /**
     * Update Rental
     *
     * Updates a rental listing. Send only the fields you want to change.
     *
     * @urlParam id integer required The rental ID. Example: 1
     */
    public function update(Request $request, int $id)
    {
        $rental = Rental::findOrFail($id);

        $validated = $request->validate([
            'title'              => 'sometimes|string|max:255',
            'description'        => 'sometimes|string',
            'category'           => ['sometimes', Rule::in(['vehicle', 'property', 'equipment', 'electronics', 'clothing', 'other'])],
            'rental_category_id' => 'nullable|exists:rental_categories,id',
            'price_per_day'      => 'sometimes|numeric|min:0',
            'currency'           => 'nullable|string|max:10',
            'location'           => 'sometimes|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'images'             => 'nullable|array',
            'owner_name'         => 'sometimes|string|max:255',
            'owner_phone'        => 'nullable|string|max:20',
            'owner_email'        => 'nullable|email',
            'status'             => ['sometimes', Rule::in(['available', 'unavailable', 'maintenance'])],
            'is_active'          => 'sometimes|boolean',
            'min_days'           => 'nullable|integer|min:1',
            'max_days'           => 'nullable|integer|min:1',
            'terms'              => 'nullable|string',
            'features'           => 'nullable|array',
        ]);

        $rental->update($validated);
        return new RentalResource($rental->load('rentalCategory'));
    }

    /**
     * Delete Rental
     *
     * Soft-deletes a rental listing.
     *
     * @urlParam id integer required The rental ID. Example: 1
     */
    public function destroy(int $id): JsonResponse
    {
        $rental = Rental::findOrFail($id);
        $rental->delete();
        return response()->json(['message' => 'Rental deleted successfully.']);
    }

    /**
     * Check Availability
     *
     * Checks if a rental is available for a given date range.
     *
     * @urlParam  id         integer required Rental ID. Example: 1
     * @queryParam start_date string required Start date (YYYY-MM-DD). Example: 2026-07-01
     * @queryParam end_date   string required End date (YYYY-MM-DD). Example: 2026-07-07
     */
    public function checkAvailability(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $rental = Rental::findOrFail($id);
        $available = $rental->isAvailableForDates($request->start_date, $request->end_date);

        return response()->json([
            'rental_id'  => $rental->id,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'available'  => $available,
        ]);
    }

    /**
     * List Categories
     *
     * Returns all active rental categories.
     */
    public function categories()
    {
        $categories = RentalCategory::active()->inorder()->get();
        return response()->json($categories);
    }
}
