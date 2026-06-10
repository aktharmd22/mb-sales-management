@php
    use App\Models\FollowUp;

    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;

    // Follow-ups needing attention (due today or overdue) — drives the nav badge.
    $dueCount = $user
        ? FollowUp::forUser($user)->pending()->whereDate('due_date', '<=', today())->count()
        : 0;

    // Primary navigation, per role.
    $nav = $isAdmin
        ? [
            ['route' => 'dashboard',       'label' => 'Dashboard',  'icon' => 'home'],
            ['route' => 'clients.index',   'label' => 'Clients',    'icon' => 'clients'],
            ['route' => 'pipeline',        'label' => 'Pipeline',   'icon' => 'pipeline'],
            ['route' => 'followups.index', 'label' => 'Follow-ups', 'icon' => 'followups', 'badge' => $dueCount],
            ['route' => 'reports',         'label' => 'Reports',    'icon' => 'reports'],
            ['label' => 'Portfolio', 'icon' => 'portfolio', 'route' => 'portfolio.index', 'children' => [
                ['route' => 'portfolio.index', 'params' => ['tab' => 'website'],    'label' => 'Websites & Software'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'video'],      'label' => 'Video Ads'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'graphic'],    'label' => 'Graphics'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'automation'], 'label' => 'Automations'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'article'],    'label' => 'Articles'],
            ]],
            ['route' => 'about.index',     'label' => 'About Us',   'icon' => 'document'],
            ['route' => 'team.index',      'label' => 'Team',       'icon' => 'team'],
            ['route' => 'targets.index',   'label' => 'Targets',    'icon' => 'targets'],
        ]
        : [
            ['route' => 'dashboard',       'label' => 'Home',       'icon' => 'home'],
            ['route' => 'clients.index',   'label' => 'Clients',    'icon' => 'clients'],
            ['route' => 'pipeline',        'label' => 'Pipeline',   'icon' => 'pipeline'],
            ['route' => 'followups.index', 'label' => 'Follow-ups', 'icon' => 'followups', 'badge' => $dueCount],
            ['label' => 'Portfolio', 'icon' => 'portfolio', 'route' => 'portfolio.index', 'children' => [
                ['route' => 'portfolio.index', 'params' => ['tab' => 'website'],    'label' => 'Websites & Software'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'video'],      'label' => 'Video Ads'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'graphic'],    'label' => 'Graphics'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'automation'], 'label' => 'Automations'],
                ['route' => 'portfolio.index', 'params' => ['tab' => 'article'],    'label' => 'Articles'],
            ]],
            ['route' => 'about.index',     'label' => 'About Us',   'icon' => 'document'],
            ['route' => 'reports',         'label' => 'Reports',    'icon' => 'reports'],
        ];

    // Mobile bottom tabs (centre is a Log Visit FAB for salespeople).
    $bottomNav = $isAdmin
        ? [
            ['route' => 'dashboard',       'label' => 'Home',     'icon' => 'home'],
            ['route' => 'clients.index',   'label' => 'Clients',  'icon' => 'clients'],
            ['route' => 'pipeline',        'label' => 'Pipeline', 'icon' => 'pipeline'],
            ['route' => 'followups.index', 'label' => 'Tasks',    'icon' => 'followups', 'badge' => $dueCount],
            ['route' => 'team.index',      'label' => 'Team',     'icon' => 'team'],
        ]
        : [
            ['route' => 'dashboard',       'label' => 'Home',    'icon' => 'home'],
            ['route' => 'clients.index',   'label' => 'Clients', 'icon' => 'clients'],
            ['route' => 'followups.index', 'label' => 'Tasks',   'icon' => 'followups', 'badge' => $dueCount],
            ['route' => 'profile',         'label' => 'Me',      'icon' => 'user'],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Malayznbeat Sales') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased bg-canvas text-ink-900 min-h-screen"
      x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen">

        {{-- ===================== Desktop sidebar ===================== --}}
        <aside class="hidden lg:flex lg:w-64 lg:flex-col fixed inset-y-0 z-30 bg-ink-900 text-white/70">
            @include('layouts.partials.brand')

            <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto no-scrollbar">
                @foreach ($nav as $item)
                    @include('layouts.partials.nav-link', $item)
                @endforeach
            </nav>

            @include('layouts.partials.sidebar-footer')
        </aside>

        {{-- ===================== Mobile drawer ===================== --}}
        <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-50 flex">
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-ink-950/60"
                 @click="sidebarOpen = false"></div>
            <aside x-show="sidebarOpen"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                   class="relative w-64 flex flex-col bg-ink-900 text-white/70">
                <button @click="sidebarOpen = false" class="absolute right-3 top-5 z-10 text-white/60 hover:text-white">
                    <x-icon name="close" class="w-6 h-6" />
                </button>
                @include('layouts.partials.brand')
                <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
                    @foreach ($nav as $item)
                        @include('layouts.partials.nav-link', $item)
                    @endforeach
                </nav>
                @include('layouts.partials.sidebar-footer')
            </aside>
        </div>

        {{-- ===================== Main column ===================== --}}
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
            @include('layouts.partials.topbar')

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 pb-28 lg:pb-10">
                <div class="mx-auto w-full max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- ===================== Mobile bottom nav ===================== --}}
    @include('layouts.partials.bottom-nav')

    {{-- ===================== Toasts ===================== --}}
    @include('layouts.partials.toasts')

    @livewireScripts
</body>
</html>
