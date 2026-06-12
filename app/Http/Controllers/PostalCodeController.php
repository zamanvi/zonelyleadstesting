<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostalCodeRequest;
use App\Models\City;
use App\Models\PostalCode;
use Illuminate\Support\Str;

class PostalCodeController extends Controller
{
    /**
     * Display a listing of postal code for a cities.
     */
    public function index(City $city)
    {
        $postalCodes = $city->postalCodes()->paginate(10);
        return view('admin.locations.postal_codes.index', compact('postalCodes', 'city'));
    }

    /**
     * Show the form for creating a new postal code for a cities.
     */
    public function create(City $city)
    {
        $postalCodes = $city->postalCodes()->paginate(10);
        return view('admin.locations.postal_codes.index', compact('postalCodes', 'city'));
    }

    /**
     * Store a newly created postalCode in storage.
     */
    public function store(PostalCodeRequest $request, City $city)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Assign parent city
        $data['city_id'] = $city->id;

        PostalCode::create($data);

        return redirect()->route('admin.cities.postal-codes.index', $city)
                         ->with('success', 'Postal code created successfully');
    }

    /**
     * Display a specific city.
     */
    public function show(City $city, PostalCode $postalCode)
    {
        $postalCodes = $city->postalCodes()->paginate(10);
        return view('admin.locations.postal_codes.show', compact('postalCodes', 'postalCode', 'city'));
    }

    /**
     * Show the form for editing a cpostalCodeity.
     */
    public function edit(City $city, PostalCode $postalCode)
    {
        $postalCodes = $city->postalCodes()->paginate(10);
        return view('admin.locations.postal_codes.edit', compact('postalCodes', 'postalCode', 'city'));
    }

    /**
     * Update a postalCode in storage.
     */
    public function update(PostalCodeRequest $request, City $city, PostalCode $postalCode)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Assign parent city
        $data['city_id'] = $city->id;

        $postalCode->update($data);

        return redirect()->route('admin.cities.postal-codes.index', $city)
                         ->with('success', 'Postal code updated successfully');
    }

    /**
     * Remove a postalCode from storage.
     */
    public function destroy(City $city, PostalCode $postalCode)
    {
        $postalCode->delete();

        return redirect()->route('admin.cities.postal-codes.index', $city)
                         ->with('success', 'Postal code deleted successfully');
    }
}