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
| 9 | Make it look good | Tailwind 4 | ⬅ Current |
| 10 | Prove it works | Pest tests, HTTP fakes | |
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

### Step 3: Style the search form and validation error

- Wrap the input and button in `flex gap-2`, and place an icon inside the input with `relative` / `absolute`.
- Change the input's colours when it has an error, using Blade's `@class([...])` directive.
- Use `sr-only` to keep the label for screen readers while hiding it visually.
- Use `hover:` / `focus:` variants for interaction states.

### Next steps

- Step 4: Style the recent searches list with icons (`<x-icon>`)
- Step 5: A `<x-weather-icon>` Blade component that picks an icon from the condition
- Step 6: Style the weather page as a card
- Step 7: For production, run `npm run build`
