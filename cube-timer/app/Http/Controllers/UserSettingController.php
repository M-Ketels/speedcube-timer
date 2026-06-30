<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class UserSettingController extends Controller
{
    public function edit(Request $request)
    {
        return \Inertia\Inertia::render('settings/Appearance', [
            'settings' => $request->user()->settings()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['theme_name' => 'default_dark']
            )
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_name' => 'required|string|max:50',
        ]);

        // Update or create the settings row
        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['theme_name' => $validated['theme_name']]
        );

        return redirect()->back();
    }
}
