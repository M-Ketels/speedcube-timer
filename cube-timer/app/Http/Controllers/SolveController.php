<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class SolveController extends Controller
{
    public function index(Request $request): Response
    {
        $solves = $request->user()->solves()->latest()->paginate(10);

        return Inertia::render('Solves/Index', [
            'solves' => $solves
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Solves/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'puzzle_type'       => 'required|string|max:50',
            'solve_time_ms'     => 'required|integer|min:0',
            'scramble'          => 'required|string|max:255',
            'penalty'           => 'nullable|string|in:+2,DNF',
        ]);

        $request->user()->solves()->create($validated);

        return redirect()->route('solvesIndex');
    }

    public function timer(): Response
    {
        return Inertia::render('Solves/Timer');
    }
}
