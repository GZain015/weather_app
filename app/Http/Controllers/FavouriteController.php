<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FavouriteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'min:2', 'max:60'],
            'country' => ['required', 'string', 'min:2', 'max:60'],
        ]);

        $request->user()->favourites()->firstOrCreate($validated);

        return back();
    }

    public function destroy(Favourite $favourite): RedirectResponse
    {
        Gate::authorize('delete', $favourite);

        $favourite->delete();

        return back();
    }
}
