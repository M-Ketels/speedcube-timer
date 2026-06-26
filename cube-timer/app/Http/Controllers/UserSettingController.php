<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserSettingController extends Controller
{
    //public function edit(Request $request): Response
    //{
    //    // Grab the settings, or create them if they don't exist yet
    //    $settings = $request->user()->settings()->firstOrCreate(
    //        [], // Search criteria (empty means just look for the user's ID)
    //        ['theme_name' => 'default_dark'] // Default values if not found
    //    );
//
    //    return Inertia::render('Settings/Index', [
    //        'settings' => $settings
    //    ]);
    //}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_name' => 'required|string|max:50',
        ]);

        $request->user()->settings()->update($validated);

        // Stay on the settings page after saving
        return redirect()->back();
    }
}
