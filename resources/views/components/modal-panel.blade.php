@props(['show', 'maxWidth' => 'max-w-lg'])

{{-- $show is the NAME of a Livewire boolean property to entangle with. --}}
<div x-data="{ show: @entangle($show) }"
     x-show="show"
     x-cloak
     @keydown.escape.window="show = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display:none;">

    <div x-show="show" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" @click="show = false"></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
        <div x-show="show"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95"
             class="relative w-full {{ $maxWidth }} bg-surface rounded-t-3xl sm:rounded-2xl shadow-pop">
            {{ $slot }}
        </div>
    </div>
</div>
