<div x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: detail.message, type: detail.type || 'success' });
            setTimeout(() => this.remove(id), detail.duration || 3500);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
     }"
     @toast.window="add($event.detail)"
     class="fixed z-[60] bottom-24 lg:bottom-6 right-4 sm:right-6 flex flex-col gap-2 w-[calc(100%-2rem)] sm:w-80 pointer-events-none">

    {{-- Surface server-side flash messages (e.g. after a redirect) as a toast. --}}
    @if (session('flash'))
        <div x-init="$nextTick(() => add({ message: @js(session('flash')), type: 'success' }))"></div>
    @endif

    <template x-for="toast in toasts" :key="toast.id">
        <div class="animate-toast-in pointer-events-auto flex items-center gap-3 rounded-xl bg-ink-900 text-white px-4 py-3 shadow-pop">
            <span class="grid place-items-center h-6 w-6 rounded-full shrink-0"
                  :class="{
                    'bg-primary': toast.type === 'success',
                    'bg-danger': toast.type === 'error',
                    'bg-accent text-ink-900': toast.type === 'info',
                  }">
                <svg x-show="toast.type === 'success'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                <svg x-show="toast.type === 'error'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                <svg x-show="toast.type === 'info'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </span>
            <p class="text-sm font-medium flex-1" x-text="toast.message"></p>
            <button @click="remove(toast.id)" class="text-white/50 hover:text-white shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </template>
</div>
