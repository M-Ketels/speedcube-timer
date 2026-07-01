<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SolveController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->user()->solves();

        if ($request->filled('puzzle_type') && $request->puzzle_type !== 'all') {
            $query->where('puzzle_type', $request->puzzle_type);
        }

        $sortBy = $request->input('sort_by', 'date_desc');
        switch ($sortBy) {
            case 'time_asc':
                $query->orderBy('solve_time_ms', 'asc');
                break;
            case 'time_desc':
                $query->orderBy('solve_time_ms', 'desc');
                break;
            case 'date_asc':
                $query->oldest();
                break;
            case 'date_desc':
            default:
                $query->latest();
                break;
        }

        $solves = $query->paginate(15)->withQueryString();

        return Inertia::render('Solves/Index', [
            'solves' => $solves,
            'filters' => [
                'puzzle_type' => $request->input('puzzle_type', 'all'),
                'sort_by' => $sortBy,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Solves/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'puzzle_type' => 'required|string|max:50',
            'solve_time_ms' => 'required|integer|min:0',
            'scramble' => 'required|string|max:255',
            'penalty' => 'nullable|string|in:+2,DNF',
        ]);

        $request->user()->solves()->create($validated);

        return redirect()->back();
    }

    public function timer(): Response
    {
        return Inertia::render('Solves/Timer');
    }
}
