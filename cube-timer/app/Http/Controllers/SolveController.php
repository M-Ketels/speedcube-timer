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

    public function export(Request $request)
    {
        $solves = $request->user()->solves()->oldest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=cube_solves_backup.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($solves) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Category', 'Time (MM:SS.SSS)', 'Scrambler', 'Date', 'Penalty +2 (yes or no)', 'DNF (yes or no)', 'Section'], ';');

            foreach ($solves as $solve) {
                // Convert DB ms back to MM:SS.SSS
                $ms = $solve->solve_time_ms;
                $mins = floor($ms / 60000);
                $secs = floor(($ms % 60000) / 1000);
                $milli = $ms % 1000;
                $timeStr = sprintf("%02d:%02d.%03d", $mins, $secs, $milli);

                // Convert DB 333 back to 3x3x3
                $cat = match($solve->puzzle_type) {
                    '333' => '3x3x3', '222' => '2x2x2', '444' => '4x4x4',
                    'pyram' => 'Pyraminx', 'minx' => 'Megaminx', 'skewb' => 'Skewb',
                    default => $solve->puzzle_type
                };

                fputcsv($file, [
                    $cat,
                    $timeStr,
                    $solve->scramble,
                    $solve->created_at->format('Y-m-d H:i'),
                    $solve->penalty === '+2' ? 'yes' : 'no',
                    $solve->penalty === 'DNF' ? 'yes' : 'no',
                    '' // Section left blank
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // max 10MB
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');

        // Skip the header row
        fgetcsv($file, 1000, ';');

        while (($row = fgetcsv($file, 1000, ';')) !== FALSE) {
            if (count($row) < 6) continue; // Skip broken rows

            // Parse Puzzle Type
            $category = strtolower($row[0]);
            $puzzleType = match(true) {
                str_contains($category, '3x3') => '333',
                str_contains($category, '2x2') => '222',
                str_contains($category, '4x4') => '444',
                str_contains($category, 'pyram') => 'pyram',
                str_contains($category, 'minx') => 'minx',
                str_contains($category, 'skewb') => 'skewb',
                default => '333'
            };

            // Parse Time (MM:SS.SSS) into MS
            $timeParts = explode(':', $row[1]);
            if (count($timeParts) == 2) {
                $mins = (int)$timeParts[0];
                $secParts = explode('.', $timeParts[1]);
                $secs = (int)$secParts[0];
                // Pad right in case it's .24 instead of .245
                $ms = isset($secParts[1]) ? (int)str_pad($secParts[1], 3, '0', STR_PAD_RIGHT) : 0;
                $totalMs = ($mins * 60000) + ($secs * 1000) + $ms;
            } else {
                continue; // Skip if format is entirely wrong
            }

            // Parse Penalties
            $plus2 = strtolower($row[4]) === 'yes';
            $dnf = strtolower($row[5]) === 'yes';
            $penalty = $dnf ? 'DNF' : ($plus2 ? '+2' : null);

            // Safely Merge
            $parsedDate = \Carbon\Carbon::parse($row[3]);

            // Manually check if this exact solve already exists
            $exists = $request->user()->solves()
                ->where('solve_time_ms', $totalMs)
                ->where('scramble', $row[2])
                ->where('created_at', $parsedDate)
                ->exists();

            // If it doesn't exist, build a new solve manually
            if (!$exists) {
                $newSolve = $request->user()->solves()->make([
                    'puzzle_type' => $puzzleType,
                    'solve_time_ms' => $totalMs,
                    'scramble' => $row[2],
                    'penalty' => $penalty,
                ]);

                $newSolve->created_at = $parsedDate;
                $newSolve->updated_at = $parsedDate;
                $newSolve->save();
            }
        }
        fclose($file);

        return redirect()->back();
    }
}
