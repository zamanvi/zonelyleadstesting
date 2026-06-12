<?php

namespace App\Http\Controllers;

use App\Http\Requests\StateRequest;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Str;

class StateController extends Controller
{
    /**
     * Display a listing of the states for a country.
     */
    public function index(Country $country)
    {
        $states = $country->states()->paginate(10);
        return view('admin.locations.states.index', compact('states', 'country'));
    }

    /**
     * Show the form for creating a new state for a country.
     */
    public function create(Country $country)
    {
        $states = $country->states()->paginate(10);
        return view('admin.locations.states.index', compact('states', 'country'));
    }

    /**
     * Store a newly created state in storage.
     */
    public function store(StateRequest $request, Country $country)
    {
        $data = $request->validated();

        // Generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Assign country from route
        $data['country_id'] = $country->id;

        State::create($data);

        return redirect()->route('admin.countries.states.index', $country)
                         ->with('success', 'State created successfully');
    }

    /**
     * Display a specific state of a country.
     */
    public function show(Country $country, State $state)
    {
        $states = $country->states()->paginate(10);
        return view('admin.locations.states.show', compact('states', 'state', 'country'));
    }

    /**
     * Show the form for editing a state.
     */
    public function edit(Country $country, State $state)
    {
        $states = $country->states()->paginate(10);
        return view('admin.locations.states.edit', compact('states', 'state', 'country'));
    }

    /**
     * Update a state in storage.
     */
    public function update(StateRequest $request, Country $country, State $state)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        // Assign country from route
        $data['country_id'] = $country->id;

        $state->update($data);

        return redirect()->route('admin.countries.states.index', $country)
                         ->with('success', 'State updated successfully');
    }

    /**
     * Remove a state from storage.
     */
    public function destroy(Country $country, State $state)
    {
        $state->delete();

        return redirect()->route('admin.countries.states.index', $country)
                         ->with('success', 'State deleted successfully');
    }
}