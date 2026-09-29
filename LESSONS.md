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
| 10 | Prove it works | Pest tests, HTTP fakes | ✅ Done |
| 11 | User favourites | Auth, relationships, policies | ⬅ Current |
| 12 | Background refresh | Queues, scheduled commands | |

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

### Step 6: Prove the cache works ✅

- `Http::fake()` also **records** every request. `Http::assertSentCount(2)` means exactly one geocoding call and one forecast call were made.
- Test 1: search `Lahore`, view its page, then search `LAHORE`. It's still only 2 requests, because `Str::slug()` makes both spellings share one cache key.
- Test 2: `$this->travel(16)->minutes()` moves the clock forward without waiting. A dataset checks both sides of the 15-minute limit: 14 minutes gives 2 requests (cached), 16 minutes gives 4 (fetched again).
- Time travel is reset automatically after each test.

✅ Check: 3 pass. Change `addMinutes(15)` to `addMinutes(10)` and only the 14-minute row fails.

### Step 7: The API is down ✅

- Three ways the API can fail: geocoding returns a 500, forecast returns a 503, or the connection fails (`Http::failedConnection()`). One test, three dataset rows.
- Dataset rows are built **before Laravel boots**, so wrap each one in `fn (): array => [...]`. Pest calls the closure when the test runs.
- Each row asserts that the user gets an error, nothing is saved, and a warning is logged.
- `Log::spy()` records log calls without writing them. `Log::shouldHaveReceived('warning')->once()` checks one happened.

✅ Check: 3 pass. Delete the `try`/`catch` in `WeatherService::request()` and the `connection timeout` row fails with a 500.

### Step 8: Test `WeatherService` directly ✅

- `php84 artisan make:test --pest Services/WeatherServiceTest` creates `tests/Feature/Services/WeatherServiceTest.php`. The folder mirrors `app/Services/`.
- `app(WeatherService::class)` builds the service from the container, the same way the controller gets it.
- `describeWeatherCode()` is private, so test it **through** the public `forCity()`, with a helper `fakeForecastWithCode(int $code)`.
- Test 1: `expect($weather)->toBe([...])` checks the complete array the service promises (the `@return` shape).
- Test 2: `Http::assertSent(fn (Request $request) => ...)` proves the forecast request uses the coordinates that geocoding returned.
- Test 3: a dataset of weather codes. Test the first and last code of each group, plus one unknown code for the `default` branch.

✅ Check: 14 pass. Then run the whole suite: `php84 artisan test --compact`.

---

## Lesson 11 — User favourites: auth, relationships, policies

**Goal:** people can register, log in, star cities as favourites, and see only their own favourites. Built by hand (no starter kit), so you see every piece.

### Step 1: Registration ✅

- `php84 artisan make:controller Auth/RegisterController` with `create()` (show the form) and `store()` (validate, create the user, log in).
- Routes inside `Route::middleware('guest')->group(...)`: logged-in users can't reach the register page.
- Validation: `unique:users` for email, `confirmed` (needs a `password_confirmation` field), `Password::defaults()`.
- `User::create($validated)` stores a **hash**, never the password, because the `User` model casts `password` to `hashed`.
- `Auth::login($user)` then `$request->session()->regenerate()`. A new session ID on login blocks session-fixation attacks.
- `<x-form-field>` anonymous component: label + input + `@error` in one tag, reused for every field.

✅ Check: register at `/register`, get redirected to `/weather`. In tinker, `User::first()->password` starts with `$2y$`.

### Step 2: Log in and log out ✅

- `php84 artisan make:controller Auth/LoginController` with three methods: `create()` (show the form), `store()` (log in) and `destroy()` (log out).
- `store()`: validate `email` + `password`, then `Auth::attempt($credentials, $request->boolean('remember'))`. It finds the user by email and checks the password against the hash. Never hash it yourself.
  - Success: `$request->session()->regenerate()`, then `redirect()->intended(route('weather.index'))`. `intended()` sends them back to the page they were trying to open before the login wall. Step 5 uses this.
  - Failure: `back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email')`. Use one vague message so attackers can't tell whether the email exists. `onlyInput` refills the email but never the password.
- `destroy()`: `Auth::logout()`, then `$request->session()->invalidate()` (throws the whole session away) and `$request->session()->regenerateToken()` (a new CSRF token). Redirect to `weather.index`.
- Routes:
  - Add `GET /login` → `create` named **`login`** and `POST /login` → `store` to the `guest` group. The name matters: the `auth` middleware redirects guests to `route('login')`.
  - Add `POST /logout` → `destroy` named `logout` in a `Route::middleware('auth')` group.
  - Logout is a **POST** with `@csrf`, not a link. If it were a GET, any site could log your users out with `<img src=".../logout">`.
- `resources/views/auth/login.blade.php`: copy the register view, keep the email and password `<x-form-field>`s, and add a "Remember me" checkbox (`name="remember"`). Link to the other page: "No account? Register", and "Already registered? Log in" on the register page.
- Header in `layouts/app.blade.php`: add `ml-auto` to a `<nav>`.
  - `@auth`: show `{{ auth()->user()->name }}` and a small `<form method="POST" action="{{ route('logout') }}">` with `@csrf` and a Log out button.
  - `@guest`: show Log in / Register links.
- `bootstrap/app.php`: `$middleware->redirectUsersTo(fn () => route('weather.index'));` inside `withMiddleware`. Without it, the `guest` middleware sends a logged-in user who opens `/login` to `/`, because there's no `dashboard` or `home` route.

✅ Check: log out, then log in with a wrong password. You see the error, and the email stays filled in but the password doesn't. Log in correctly and your name shows in the header. While logged in, `/login` sends you to `/weather`.

### Step 3: The `favourites` table ✅

- `php84 artisan make:model Favourite -mf` creates the model, the migration and the factory, like `Search` in Lesson 8.
- Migration `up()`:

  ```php
  Schema::create('favourites', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('city');
      $table->string('country');
      $table->timestamps();

      $table->unique(['user_id', 'city', 'country']);
  });
  ```

  - `foreignId('user_id')` is an unsigned big integer, the same type as `users.id`.
  - `constrained()` works out the table from the column name (`user_id` → `users.id`) and adds a **foreign key**. The database then refuses a favourite that points at a user who doesn't exist.
  - `cascadeOnDelete()` deletes a user's favourites along with the user, so no orphan rows are left behind.
  - `unique(['user_id', 'city', 'country'])` stops a user from starring the same city twice, but two *different* users can both star Lahore.
  - It stores `city` + `country`, **not** a link to `searches`. `searches` is shared history that can be cleaned up at any time, and a favourite must survive that.
- Model: `#[Fillable(['city', 'country'])]`. Leave `user_id` **out**. Otherwise someone could post `user_id=2` and create a favourite for another user. Step 5 sets it safely through the relationship.
- Factory `definition()`: `'user_id' => User::factory()`, plus `fake()->city()` and `fake()->country()`. `User::factory()` inside a factory creates a user for each favourite, unless you pass one in.
- `php84 artisan migrate`

✅ Check in `php84 artisan tinker`:
- `$favourite = App\Models\Favourite::factory()->create();` creates a user **and** a favourite.
- `App\Models\Favourite::factory()->create(['user_id' => $favourite->user_id, 'city' => $favourite->city, 'country' => $favourite->country]);` throws a `UniqueConstraintViolationException`.
- `App\Models\User::find($favourite->user_id)->delete();` then `App\Models\Favourite::count()` is `0`: the cascade worked.
- Deleting that user was also the clean-up. Your own registered account is untouched.

### Step 4: Relationships ✅

- In `User`, add a `favourites()` method that returns `$this->hasMany(Favourite::class)`. One user **has many** favourites.
- In `Favourite`, add a `user()` method that returns `$this->belongsTo(User::class)`. Each favourite **belongs to** one user. "Belongs to" goes on the model whose table holds the foreign key (`favourites.user_id`).
- Eloquent guesses the keys from the names: `hasMany` looks for `user_id` on `favourites`, and `belongsTo` uses the method name `user` + `_id`. That's why naming conventions matter.
- Return types and docblocks, so your editor and PHPStan know what comes back:

  ```php
  /**
   * @return HasMany<Favourite, $this>
   */
  public function favourites(): HasMany
  ```

  Do the same for `user()` with `BelongsTo<User, $this>`. Import `Illuminate\Database\Eloquent\Relations\HasMany` / `BelongsTo`.
- **Method vs property:** this is the key idea of the step.
  - `$user->favourites()` (with brackets) returns a **query builder**. You can chain onto it (`->where(...)->latest()->get()`) or `->create([...])` through it.
  - `$user->favourites` (no brackets) runs the query and returns a **Collection** of `Favourite` models. It's loaded once, then cached on the model.
- `$user->favourites()->create(['city' => 'Lahore', 'country' => 'Pakistan'])` fills in `user_id` for you. That's why `user_id` could stay out of `#[Fillable]` in Step 3: the relationship sets it, never the request.
- Factories understand relationships too:
  - `User::factory()->has(Favourite::factory()->count(3))->create()` creates a user with 3 favourites.
  - `Favourite::factory()->for($user)->create()` creates a favourite for an existing user, instead of the new user the factory would normally make.

✅ Check in `php84 artisan tinker`:
- `$user = App\Models\User::factory()->has(App\Models\Favourite::factory()->count(3))->create();`
- `$user->favourites` shows a Collection of 3. `$user->favourites()->count()` is `3`, counted by the database.
- `$user->favourites()->create(['city' => 'Lahore', 'country' => 'Pakistan']);` then `$user->favourites()->count()` is `4`.
  - `$user->favourites->count()` still says `3`, because the property was loaded before. `$user->refresh()` reloads it.
- `App\Models\Favourite::first()->user->email` goes the other way, from a favourite to its owner.
- Clean up: `$user->delete();` The cascade removes their 4 favourites.

### Step 5: Star / unstar a city from the weather page ✅

**Goal:** a logged-in user sees a star button on the weather page. Click it to save the city, click again to remove it. Guests get sent to log in.

#### 5a. Controller and routes

- `php84 artisan make:controller FavouriteController` with `store()` and `destroy()`.
- `store(Request $request): RedirectResponse`
  - Validate `city` and `country`: `['required', 'string', 'max:100']`.
  - `$request->user()->favourites()->firstOrCreate($validated);`
    - Going **through the relationship** fills in `user_id` from the logged-in user. The request never decides whose favourite it is.
    - `firstOrCreate` instead of `create`: a double-click finds the existing row, where `create` would crash on the unique index from Step 3.
  - `return back();`
- `destroy(Favourite $favourite): RedirectResponse`
  - **Route model binding:** name the route parameter `{favourite}` and type-hint `Favourite $favourite`. Laravel loads the row by its ID for you, or returns a 404 if it doesn't exist.
  - `$favourite->delete(); return back();`
  - ⚠️ **This has a security hole on purpose:** any logged-in user can delete *anyone's* favourite by changing the ID. You'll prove it and fix it with a policy in Step 7.
- Routes go in your existing `Route::middleware('auth')` group, next to logout:
  - `POST /favourites` → `store`, named `favourites.store`
  - `DELETE /favourites/{favourite}` → `destroy`, named `favourites.destroy`
- `auth` middleware: a guest who hits these routes is redirected to `route('login')`. This is why that route name mattered in Step 2.

#### 5b. Tell the page whether the city is already starred

- In `WeatherController::show()`, add `Request $request` as the first parameter, then look up the favourite *after* the 404 check:

  ```php
  $favourite = $request->user()?->favourites()
      ->where('city', $data['city'])
      ->where('country', $data['country'])
      ->first();

  return view('weather.show', [...$data, 'favourite' => $favourite]);
  ```

  - `?->` is the **nullsafe operator**. For a guest, `user()` is `null`, so the whole chain stops and `$favourite` is `null` instead of crashing.
  - `[...$data, 'favourite' => $favourite]` spreads the weather array and adds one more key.
  - Use `$data['city']` (the API's spelling, e.g. `Lahore`), not the URL's `$city` (`lahore`). That's what `store()` saved.

#### 5c. The star button in `weather/show.blade.php`

Put it in the card header, next to the `<h1>`:

- `@if ($favourite)`: a form to `route('favourites.destroy', $favourite)` with `@csrf` and **`@method('DELETE')`**, plus a filled star button: `<x-icon name="star" class="size-6 fill-current text-amber-400" />`.
  - HTML forms can only send GET and POST. `@method('DELETE')` adds a hidden `_method` field, and Laravel treats the POST as a DELETE.
- `@else`: a form to `route('favourites.store')` with `@csrf`, two hidden inputs (`city` and `country`), and an outline star: `<x-icon name="star" class="size-6 text-slate-400" />`.
- Show the `@else` form to guests too. When they click it, `auth` sends them to log in, and after logging in, `intended()` brings them back **to this weather page**. For a POST, Laravel remembers the page the form was on, not the POST URL.
- Give each button an `aria-label` ("Save to favourites" / "Remove from favourites"). An icon-only button needs one for screen readers.

✅ Check:
- Logged in, click the star: it fills in. In tinker, `App\Models\User::first()->favourites` shows the city.
- Click it again: it empties, and the row is gone.
- Double-click fast: still one row, no error.
- Log out, then click the star: you land on `/login`. Log in, and you're back on the same weather page (click once more to save).

### Step 6: "Your favourites" on the search page ✅

**Goal:** a logged-in user sees their starred cities above "Recent Searches", sorted A–Z, each linking to its weather page with a button to remove it. Guests see nothing new.

#### 6a. Controller: `WeatherController::index()`

- Add `Request $request` as a parameter, and pass one more key to the view:

  ```php
  'favourites' => $request->user()?->favourites()->orderBy('city')->get() ?? collect(),
  ```

  - `favourites()` **with brackets** is a query, so you can sort it in the database with `orderBy('city')`, then `get()` runs it. The property `->favourites` would load every row unsorted.
  - A guest's `user()` is `null`, so `?->` makes the whole chain `null`. `?? collect()` swaps that for an **empty Collection**, so the view can always call `$favourites->isEmpty()` without first checking whether the user is logged in.
- **Scoping:** the list comes from `$request->user()->favourites()`, so it only ever contains the logged-in user's rows. Never do `Favourite::all()` here: that would show everyone's favourites.

#### 6b. View: `weather/index.blade.php`

Add a new `<section>` between the search form and Recent Searches. Copy the Recent Searches card styling (`rounded-xl border divide-y ...`) so the two lists match.

- Wrap it in `@auth ... @endauth`: guests don't get the section at all.
- Heading: `<x-icon name="star" class="size-4" />` + "Your Favourites", styled like the "Recent Searches" `<h2>`.
- Use **`@forelse ($favourites as $favourite) ... @empty ... @endforelse`**. It's a `@foreach` with a built-in "nothing to show" branch.
  - Each `<li>` is a `flex` row with **two siblings**:
    1. `<a href="{{ route('weather.show', ['city' => $favourite->city]) }}" class="min-w-0 grow ...">` with the city and country (`truncate`).
    2. A DELETE form to `route('favourites.destroy', $favourite)` (`@csrf` + `@method('DELETE')`), with a filled star button and `aria-label="Remove {{ $favourite->city }} from favourites"`.
  - **Don't put the form inside the `<a>`.** Interactive elements can't be nested: a click on the button would also count as a click on the link.
  - `@empty`: a friendly hint, e.g. "Star a city on its weather page to see it here."
- `destroy()` already returns `back()`, so removing a city from this list reloads the search page. No controller changes are needed.

✅ Check:
- Logged in, star 3 cities. The search page lists them A–Z, and each link opens its weather page.
- Click a star in the list: that city disappears, and its weather page shows the empty star again.
- Remove them all: the "Star a city…" hint appears.
- Register a second account: its list is empty, and it can't see the first user's cities.
- Log out: no favourites section at all.

### Step 7: A policy, so nobody can delete someone else's favourite

**Authentication** asks *who are you?* (Steps 1–2). **Authorization** asks *are you allowed to do this?* A **policy** is a class that answers that for one model.

#### 7a. Prove the hole first

1. In tinker, create a favourite for somebody else and note its ID:
   `App\Models\Favourite::factory()->create(['city' => 'Oslo', 'country' => 'Norway'])->id`
2. In the browser, log in as **your** account, star any city, and open the search page.
3. Open DevTools (F12) → Elements. Find your remove form: `<form method="POST" action=".../favourites/1">`. Double-click the action and change the number to Oslo's ID.
4. Click that star. Then, in tinker, `App\Models\Favourite::where('city', 'Oslo')->exists()` returns `false`: you deleted someone else's data.

Never trust an ID that comes from the browser. Anyone can edit HTML or send a request by hand.

#### 7b. Write the policy

- `php84 artisan make:policy FavouritePolicy` creates `app/Policies/FavouritePolicy.php`. Leave off `--model`: it would generate 7 stub methods, and you only need one.
- Add a `delete()` method:

  ```php
  public function delete(User $user, Favourite $favourite): bool
  {
      return $user->id === $favourite->user_id;
  }
  ```

  - Laravel passes in the **logged-in user** automatically. You only pass the favourite.
  - Use `===` (strict), so `1 === '1'` can't sneak past.
- **Policy discovery:** there's nothing to register. `Favourite` model + `App\Policies\FavouritePolicy` is the naming convention Laravel looks for.

#### 7c. Use it in `FavouriteController::destroy()`

- As the **first line**, before the delete:

  ```php
  Gate::authorize('delete', $favourite);
  ```

  (`use Illuminate\Support\Facades\Gate;`)
- It finds `FavouritePolicy` from the model's class, calls `delete($currentUser, $favourite)`, and if that returns `false`, it throws an exception. Laravel turns that into a **403 Forbidden** page, and the delete line never runs.
- Other ways to call the same policy, for reference:
  - On the route: `->can('delete', 'favourite')`. It runs before the controller at all.
  - In Blade: `@can('delete', $favourite) ... @endcan` hides a button the user isn't allowed to use. It's handy for display, but it's **not** protection on its own: the route must still check.

✅ Check:
- Repeat 7a with a new Oslo favourite. You now get a **403** page, and `Favourite::where('city', 'Oslo')->exists()` is still `true`.
- Removing your **own** favourites still works, from both the weather page and the search page.
- Clean up Oslo: `App\Models\Favourite::where('city', 'Oslo')->first()->user->delete();` deletes the factory user, and the cascade removes Oslo.

**Bonus:** a 403 admits *"this favourite exists, it's just not yours"*. To reveal nothing, return `$user->id === $favourite->user_id ? Response::allow() : Response::denyAsNotFound();` from the policy (`use Illuminate\Auth\Access\Response;`, and change the return type to `Response`). Then it's a 404, the same as an ID that doesn't exist.

### Next steps

- Step 8: Tests: `actingAs()`, guests redirected, ownership enforced

