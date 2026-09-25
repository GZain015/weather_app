# Weather App — Laravel Learning Plan

One lesson at a time: type the code, run it, verify it works, then move on.

Run all commands from this folder (`weather_app/`), using `php84` / `composer84`.

## Roadmap

| # | You build | You learn | Status |
|---|---|---|---|
| 1 | First weather route | Request lifecycle, routing | ✅ Done |
| 2 | Move logic to a controller | Controllers, `make:` commands | ✅ Done |
| 3 | A real HTML page | Blade templates & layouts | ✅ Done |
| 4 | City search form | Forms, CSRF, validation | ✅ Done |
| 5 | Call Open-Meteo | HTTP client | ✅ Done |
| 6 | Tidy the code | Config, `.env`, service class, DI, error handling | ✅ Done |
| 7 | Stop hammering the API | Caching | ✅ Done |
| 8 | Save search history | Migrations, Eloquent, factories | ✅ Done |
| 9 | Make it look good | Tailwind 4 | ✅ Done |
| 10 | Prove it works | Pest tests, HTTP fakes | ⬅ Current |
| 11 | User favourites | Auth, relationships, policies | |
| 12 | Background refresh | Queues, scheduled commands | |

## Leftover fix from Lesson 7

In `app/Services/WeatherService.php`, add a colon to the cache key:

```php
$cacheKey = 'weather:'.Str::slug($city);
```

---

## Lesson 8 — Migrations, Eloquent & factories

**Goal:** save every successful search to the database and show the 5 most recent ones on the search page.

### Step 1: Generate the model, migration and factory

```bash
php84 artisan make:model Search -mf
```

- `-m` creates a migration and `-f` creates a factory.
- Naming rule: the model is **singular** (`Search`) and the table is **plural** (`searches`). Eloquent works out the table name for you.

New files:

```
app/Models/Search.php
database/migrations/xxxx_xx_xx_xxxxxx_create_searches_table.php
database/factories/SearchFactory.php
```

### Step 2: Define the table

In the new migration, replace `up()` with:

```php
public function up(): void
{
    Schema::create('searches', function (Blueprint $table) {
        $table->id();
        $table->string('city');
        $table->string('country');
        $table->decimal('temperature', 5, 2);
        $table->string('condition');
        $table->timestamps();
    });
}
```

- `id()` is an auto-incrementing primary key.
- `decimal(5, 2)` stores exact numbers from -999.99 to 999.99. A `float` can pick up small rounding errors.
- `timestamps()` adds `created_at` and `updated_at` columns, and Eloquent fills them in automatically.

### Step 3: Run the migration

```bash
php84 artisan migrate
php84 artisan migrate:status
```

A migration is version control for your database schema. Laravel records which migrations have already run, so `migrate` only runs new ones. `php84 artisan migrate:rollback` undoes the last batch.

✅ Check: `create_searches_table` shows **Ran**.

### Step 4: Set up the `Search` model ✅

`#[Fillable([...])]` lists the columns `Search::create()` may fill, which blocks mass assignment. `casts()` formats `temperature` as `decimal:1`.

### Step 5: Save a search after each successful lookup ✅

In `WeatherController::show()`, call `Search::create([...])` **after** the 404 check, using the API's data (`$data['city']`) rather than what the user typed.

✅ Check: `php84 artisan tinker` → `App\Models\Search::latest()->first();` shows the last city.

### Step 6: Show the 5 most recent searches on the search page ✅

- In `index()`, pass `Search::latest()->take(5)->get()` to the view as `recentSearches`.
- In `weather/index.blade.php`, loop over it with `@foreach` and link each city with `route('weather.show', ...)`.

### Step 7: Factories — fake data on demand ✅

- Fill `SearchFactory::definition()` with `fake()->city()`, `fake()->country()`, `fake()->randomFloat(1, -10, 45)`, `fake()->randomElement([...])`.
- Add a `rainy()` state with `$this->state(...)`.
- Try in tinker: `Search::factory()->make()` (not saved) vs `Search::factory()->count(3)->create()` (saved) vs `Search::factory()->rainy()->create()`.
- Clean up afterwards with `Search::truncate()`.

### Step 8: Stop duplicate rows on refresh

- Rule: **GET requests must not change data.** Refreshing a page repeats its GET request, so saving in `show()` creates duplicates.
- Move the lookup and `Search::create()` into `search()` (the POST). An unknown city now sends the user back to the form with `back()->withErrors([...])->withInput()` instead of showing a 404.
- `show()` only displays. The cache means it doesn't call the API a second time.
- Redirecting after the POST (Post/Redirect/Get) stops the browser from asking to "resubmit the form".

✅ Check: search once, refresh the weather page 5 times, and the count goes up by 1. An unknown city shows a red error under the input.

**Status: ✅ done**

### Step 9: One row per city (bonus) ✅

- Delete existing duplicates, keeping the newest row: `Search::whereNotIn('id', Search::selectRaw('max(id)')->groupBy('city', 'country'))->delete();`
- Add a migration with `$table->unique(['city', 'country'])` so the database itself refuses duplicates.
- In `search()`, replace `Search::create()` with `Search::updateOrCreate([...match...], [...values...])->touch();`. `touch()` bumps `updated_at` even when the weather hasn't changed.
- In `index()`, sort with `latest('updated_at')` so the most recently searched city comes first.

✅ Check: search Lahore 3 times, and there's still 1 Lahore row, at the top of the list.

---

## Lesson 9 — Make it look good with Tailwind 4

**Goal:** turn the plain HTML pages into a clean, styled weather app using Tailwind utility classes, Vite and Blade components.

Run two terminals while working on this lesson: `php84 artisan serve` and `npm run dev`.

### Step 1: Load Tailwind through Vite ✅

- Add `@vite(['resources/css/app.css', 'resources/js/app.js'])` to the `<head>` in `layouts/app.blade.php`.
- Run `npm run dev` in a second terminal. Vite rebuilds the CSS and refreshes the browser on every save.
- ✅ Check: the font changes to Instrument Sans and the default page margins disappear (Tailwind's reset, called Preflight).

### Step 2: Style the layout ✅

- Page background, a centred column (`mx-auto max-w-xl`), padding, and a header linking home.

### Step 3: Style the search form and validation error ✅

- Wrap the input and button in `flex gap-2`, and place an icon inside the input with `relative` / `absolute`.
- Change the input's colours when it has an error, using Blade's `@class([...])` directive.
- Use `sr-only` to keep the label for screen readers while hiding it visually.
- Use `hover:` / `focus:` variants for interaction states.

### Step 4: Style the recent searches list ✅

- A white card with `rounded-xl border shadow-sm`, and `divide-y` for lines between rows.
- Make each row one clickable link (`flex justify-between`), with the temperature pushed to the right.
- Use `min-w-0` + `truncate` so long city names end with "…" instead of breaking the layout, and `shrink-0` so the temperature never gets squeezed.

### Step 5: A `<x-weather-icon>` component ✅

- An **anonymous component** is just a Blade file in `resources/views/components/`, with no PHP class. `weather-icon.blade.php` becomes `<x-weather-icon>`.
- `@props(['condition'])` turns `condition="Rain"` into a `$condition` variable. Every other attribute (like `class`) lands in `$attributes`.
- A `match` picks the Lucide icon and colour for each condition. Use the same strings `describeWeatherCode()` returns.
- `{{ $attributes->class($color) }}` passes the caller's attributes on to `<x-icon>` and adds the colour class.
- Use it in the recent searches list, before the city name.

✅ Check: each recent search shows an icon that matches its condition, e.g. a yellow sun for "Clear sky".

### Step 6: Style the weather page as a card ✅

- Rewrite the content section of `weather/show.blade.php` as one white card: a header with the city, a huge temperature and the condition, plus a large `<x-weather-icon>`. That's the Step 5 component reused at a different size.
- Show exact temperature and wind side by side in a stats strip: `<dl class="grid grid-cols-2 divide-x">`, with `<dt>`/`<dd>` for label and value.
- `round($temperature)` for the big number, `tabular-nums` so digits line up.
- Add a back link above the card with the `arrow-left` icon.

✅ Check: search a city, and the weather page shows a card with a matching icon and no layout break on a narrow window.

### Step 7: Build for production ✅

- `npm run dev` writes `public/hot`, which tells `@vite` to load files from the Vite dev server. Stop it (Ctrl+C) and the file is removed.
- `npm run build` writes minified, hashed files to `public/build/` plus `manifest.json`. `@vite` reads the manifest to find the right filenames.
- Tailwind only ships the classes it finds in your templates, so the CSS stays small.
- `public/build` and `public/hot` are in `.gitignore`: build on the server, don't commit the output.

✅ Check: with only `php84 artisan serve` running, the app still looks styled, and View Source shows `/build/assets/app-xxxx.css`.

---

## Lesson 10 — Prove it works with Pest

**Goal:** a test suite that proves search, validation, history, caching and error handling work, and never calls the real Open-Meteo API.

Run tests with `php84 artisan test --compact` (the whole suite) or `php84 artisan test --compact --filter="recent"` (tests whose names match).

### Step 1: How the test environment works ✅

- `phpunit.xml` overrides `.env` during tests: `DB_DATABASE=:memory:` (a fresh SQLite database in RAM), `CACHE_STORE=array` (the cache is emptied after every test), `SESSION_DRIVER=array`.
- `tests/Pest.php` binds every test in `tests/Feature` to Laravel's `TestCase`, which boots the app and gives you `$this->get()`, `$this->post()` and so on.
- In `tests/Pest.php`, change the commented `->use(RefreshDatabase::class)` to `->use(LazilyRefreshDatabase::class)` and update the `use` import. Every test then starts with an empty, freshly migrated database. The "lazily" part means migrations only run for tests that actually touch the database.

✅ Check: `php84 artisan test --compact` still shows 4 passing tests.

### Step 2: Your first feature test: the recent searches list ✅

- `php84 artisan make:test --pest WeatherControllerTest` creates `tests/Feature/WeatherControllerTest.php`.
- Every test has three parts, separated by a blank line: **arrange** (create data with factories), **act** (make one request), **assert** (check the response).
- Test 1: create 6 searches with different `updated_at` values, then `assertSeeInOrder([...5 newest...])` and `assertDontSee('<oldest>')`.
- Test 2: with an empty database, `assertDontSee('Recent Searches')`.
- Give fixed values (`'country' => 'Testland'`) to anything you assert on, so random factory data can't accidentally match.

✅ Check: both tests pass. Then break the code on purpose: change `take(5)` to `take(6)` and watch the test fail and name the problem. Put it back afterwards.

### Step 3: Validation tests with a dataset ✅

- One test, many inputs: `->with([...])` runs the test once per row, and the row's name (`'too short'`) shows in the output.
- `$this->from(route('weather.index'))` sets the page the request "came from", so `back()` has somewhere to redirect to.
- `assertSessionHasErrors(['city' => '<exact message>'])` checks the message the user actually sees, not just that some error exists.
- `assertDatabaseCount('searches', 0)` proves a failed search saves nothing.

✅ Check: 3 dataset rows pass. Change `min:2` to `min:3` and watch only the `too short` row fail.

### Step 4: Fake the Open-Meteo API ✅

- `Http::fake(['url-pattern*' => Http::response([...])])` answers matching requests with your JSON. The real API is never called. `*` is a wildcard for the query string.
- `Http::preventStrayRequests()` makes any unfaked request fail (as a 500 with `StrayRequestException`) instead of silently hitting the internet.
- Put the fake in a helper function, `fakeOpenMeteo()`, at the top of the test file so every test can reuse it.
- Test 1: POST `lahore`, then assert the redirect **and** `assertDatabaseHas('searches', [...])` with the API's spelling (`Lahore`) and the translated condition (`Clear sky` for code 0).
- Test 2: GET the weather page, then `assertSeeInOrder(['Lahore', 'Pakistan', 'Clear sky', '9.2 km/h'])`.

✅ Check: both pass. Comment out the `Http::fake([...])` call and see the "without a matching fake" error.

### Step 5: Unknown city ✅

- When nothing matches, Open-Meteo returns JSON **without** a `results` key. Fake exactly that with a second helper, `fakeUnknownCity()`, that fakes only the geocoding URL.
- The forecast URL is deliberately not faked. If the code called it anyway, `preventStrayRequests()` would fail the test, which proves the service stops after a failed lookup.
- POST test: `assertSessionHasErrors([...exact message...])`, `assertSessionHasInput('city', 'Atlantis')` (the form keeps what they typed), and `assertDatabaseCount('searches', 0)`.
- GET test: `assertNotFound()`, the named version of `assertStatus(404)`.

✅ Check: both pass. Remove `->withInput()` from the controller and watch the `assertSessionHasInput` line fail.

### Step 6: Prove the cache works

- `Http::fake()` also **records** every request. `Http::assertSentCount(2)` means exactly one geocoding call and one forecast call were made.
- Test 1: search `Lahore`, view its page, then search `LAHORE`. It's still only 2 requests, because `Str::slug()` makes both spellings share one cache key.
- Test 2: `$this->travel(16)->minutes()` moves the clock forward without waiting. A dataset checks both sides of the 15-minute limit: 14 minutes gives 2 requests (cached), 16 minutes gives 4 (fetched again).
- Time travel is reset automatically after each test.

✅ Check: 3 pass. Change `addMinutes(15)` to `addMinutes(10)` and only the 14-minute row fails.

### Next steps

- Step 7: API down: timeouts and 500s show a friendly error instead of crashing
- Step 8: Test `WeatherService` directly: a dataset of weather codes → condition text

