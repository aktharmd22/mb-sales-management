@php
    $isAdmin = auth()->user()?->isAdmin() ?? false;
@endphp

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-surface border-t border-ink-100 pb-[env(safe-area-inset-bottom)]">
    <div class="relative grid {{ $isAdmin ? 'grid-cols-5' : 'grid-cols-5' }} h-16">

        @if (! $isAdmin)
            {{-- Split tabs around a centre Log Visit FAB --}}
            @php
                $left = array_slice($bottomNav, 0, 2);
                $right = array_slice($bottomNav, 2);
            @endphp

            @foreach ($left as $item)
                @include('layouts.partials.bottom-tab', $item)
            @endforeach

            {{-- Centre FAB --}}
            <div class="grid place-items-center">
                <a href="{{ route('visits.create') }}" wire:navigate title="Client Visit"
                   class="-mt-7 grid place-items-center h-14 w-14 rounded-2xl bg-primary text-white shadow-pop ring-4 ring-canvas transition active:scale-95">
                    <x-icon name="plus" class="w-7 h-7" />
                </a>
            </div>

            @foreach ($right as $item)
                @include('layouts.partials.bottom-tab', $item)
            @endforeach
        @else
            @foreach ($bottomNav as $item)
                @include('layouts.partials.bottom-tab', $item)
            @endforeach
        @endif
    </div>
</nav>
