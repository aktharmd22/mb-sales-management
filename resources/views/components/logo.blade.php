@props(['class' => 'h-8'])

@php
    // Drop your logo at: public/images/logo.(svg|png|webp|jpg). Any of these is picked up.
    // Rendered height-driven (auto width) so wide wordmarks display correctly.
    // Falls back to the teal "M" mark if no file is present.
    $exts = ['svg', 'png', 'webp', 'jpg', 'jpeg'];
    $ext = collect($exts)->first(fn ($e) => file_exists(public_path("images/logo.$e")));
@endphp

@if ($ext)
    <img src="{{ asset("images/logo.$ext") }}?v={{ filemtime(public_path("images/logo.$ext")) }}"
         alt="{{ config('app.name') }}"
         {{ $attributes->merge(['class' => $class . ' w-auto object-contain rounded-lg shrink-0']) }} />
@else
    <span {{ $attributes->merge(['class' => 'grid place-items-center h-9 w-9 rounded-xl bg-primary text-white font-bold text-lg shadow-sm shrink-0']) }}>M</span>
@endif
