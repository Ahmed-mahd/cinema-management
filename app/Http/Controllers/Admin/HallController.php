<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HallController extends Controller
{
    public function index()
    {
        $halls = Hall::withCount('showtimes')->get();

        return Inertia::render('Admin/Halls/Index', [
            'halls' => $halls,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Halls/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rows' => 'required|integer|min:1',
            'columns' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['capacity'] = $validated['rows'] * $validated['columns'];

        Hall::create($validated);

        return redirect()->route('admin.halls.index')
            ->with('success', 'Hall created successfully.');
    }

    public function edit(Hall $hall)
    {
        return Inertia::render('Admin/Halls/Edit', [
            'hall' => $hall,
        ]);
    }

    public function update(Request $request, Hall $hall)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rows' => 'required|integer|min:1',
            'columns' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['capacity'] = $validated['rows'] * $validated['columns'];

        $hall->update($validated);

        return redirect()->route('admin.halls.index')
            ->with('success', 'Hall updated successfully.');
    }

    public function destroy(Hall $hall)
    {
        if ($hall->showtimes()->exists()) {
            return back()->with('error', 'Cannot delete hall with existing showtimes.');
        }

        $hall->delete();

        return redirect()->route('admin.halls.index')
            ->with('success', 'Hall deleted successfully.');
    }
} 