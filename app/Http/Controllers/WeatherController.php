<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use App\Models\Search;

class WeatherController extends Controller
{

    public function __construct(private readonly WeatherService $weather){}


    public function index(): View
    {
        return view('weather.index', [
            'recentSearches' => Search::latest('updated_at')->take(5)->get(),
        ]);
    }

    public function search(Request $request) : RedirectResponse
    {
        $validated = $request->validate([
            "city" => ['required', 'string', 'min:2', 'max:60'],
        ]);

        $data = $this->weather->forCity($validated['city']);

        if ($data === null) {
            return back()
                ->withErrors(['city' => "We couldn't find weather for \"{$validated['city']}\"."])
                ->withInput();
        }

        Search::updateOrCreate(
            ['city' => $data['city'], 'country' => $data['country']],
            ['temperature' => $data['temperature'], 'condition' => $data['condition']],
        )->touch();

        return redirect()->route('weather.show', ['city' => $validated['city']]);
    }

    public function show(string $city) : View 
    { 
        $data = $this->weather->forCity($city);

        if ($data=== null){
            abort(404, "We couldn't find weather for \"{$city}\".");
        }

        return view('weather.show', $data);
    }

}
