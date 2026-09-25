@props(['name', 'label', 'type' => 'text'])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name) }}"
        {{ $attributes->class([
            'mt-1 w-full rounded-xl border bg-white px-4 py-3 shadow-sm outline-none focus:ring-4',
            'border-slate-300 focus:border-sky-500 focus:ring-sky-100' => ! $errors->has($name),
            'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>