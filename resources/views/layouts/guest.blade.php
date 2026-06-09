<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Malayznbeat Sales') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900">
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden px-5 py-10 bg-[#0a1733]">

        {{-- ===== Neat bluish background ===== --}}
        <div class="absolute inset-0 bg-gradient-to-br from-[#0c2150] via-[#0a1733] to-[#06112a]"></div>
        <div class="absolute -top-40 -left-40 h-[28rem] w-[28rem] rounded-full bg-blue-500/25 blur-[130px]"></div>
        <div class="absolute -bottom-44 -right-32 h-[28rem] w-[28rem] rounded-full bg-indigo-500/25 blur-[130px]"></div>
        <div class="absolute top-1/3 right-1/3 h-72 w-72 rounded-full bg-sky-400/15 blur-[120px]"></div>
        {{-- subtle dot grid --}}
        <div class="absolute inset-0 opacity-[0.05]"
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>

        {{-- ===== Card ===== --}}
        <div class="relative w-full max-w-md">
            <div class="flex justify-center mb-6">
                <x-logo class="h-11 max-w-[220px]" />
            </div>

            <div class="rounded-3xl bg-white shadow-2xl shadow-blue-950/40 ring-1 ring-white/10 p-7 sm:p-9">
                {{ $slot }}
            </div>

            <p class="text-center text-xs text-white/40 mt-6">© {{ date('Y') }} Malayznbeat · Built for the field.</p>
        </div>
    </div>
</body>
</html>
