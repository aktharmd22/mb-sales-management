<div x-data="{ uploadOpen: {{ ($errors->any() && $isAdmin) ? 'true' : 'false' }} }">
    <x-page-header title="About Us" subtitle="Company materials to show clients during a visit.">
        @if ($isAdmin)
            <x-slot:actions>
                <button type="button" @click="uploadOpen = true" class="btn-primary">
                    <x-icon name="upload" class="w-4 h-4" /> Upload PDF
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    @if ($documents->isEmpty())
        <x-empty-state icon="document"
            title="No company documents yet"
            message="{{ $isAdmin ? 'Upload the company profile so the whole team can present it on a visit.' : 'Your manager hasn\'t uploaded any materials yet — check back soon.' }}">
            @if ($isAdmin)
                <x-slot:action>
                    <button type="button" @click="uploadOpen = true" class="btn-primary"><x-icon name="upload" class="w-4 h-4" /> Upload PDF</button>
                </x-slot:action>
            @endif
        </x-empty-state>
    @else
        <div class="grid lg:grid-cols-[320px,1fr] gap-5">
            {{-- Document list --}}
            <div class="space-y-2">
                @foreach ($documents as $doc)
                    <div wire:key="doc-{{ $doc->id }}"
                         class="card p-4 cursor-pointer transition border-2 {{ $selected && $selected->id === $doc->id ? 'border-primary' : 'border-transparent hover:border-ink-100' }} {{ ! $doc->is_active ? 'opacity-60' : '' }}"
                         wire:click="select({{ $doc->id }})">
                        <div class="flex items-start gap-3">
                            <span class="grid place-items-center h-10 w-10 rounded-xl bg-danger/10 text-danger shrink-0">
                                <x-icon name="document" class="w-5 h-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-ink-900 truncate">{{ $doc->title }}</p>
                                <p class="text-xs text-ink-700/60">{{ $doc->readableSize() }} · PDF</p>
                                @if ($doc->description)
                                    <p class="text-xs text-ink-700/60 mt-1 line-clamp-2">{{ $doc->description }}</p>
                                @endif
                                @if ($isAdmin && ! $doc->is_active)
                                    <span class="badge bg-ink-100 text-ink-700 mt-1.5">Hidden</span>
                                @endif
                            </div>
                        </div>

                        @if ($isAdmin)
                            <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-ink-50" wire:click.stop>
                                <button wire:click="toggleActive({{ $doc->id }})" class="text-xs font-medium px-2.5 py-1.5 rounded-lg {{ $doc->is_active ? 'text-ink-700 hover:bg-ink-50' : 'text-primary hover:bg-primary/10' }}">
                                    {{ $doc->is_active ? 'Hide' : 'Show' }}
                                </button>
                                <button wire:click="delete({{ $doc->id }})" wire:confirm="Remove this document for everyone?"
                                        class="text-xs font-medium px-2.5 py-1.5 rounded-lg text-danger hover:bg-danger/10">
                                    Delete
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- PDF viewer --}}
            <div class="card overflow-hidden flex flex-col" x-data="{ full: false }"
                 @keydown.escape.window="full = false">
                @if ($selected)
                    <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-ink-100">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-900 truncate">{{ $selected->title }}</p>
                            <p class="text-xs text-ink-700/50">{{ $selected->readableSize() }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="full = true" class="btn-ghost text-sm" title="Full view">
                                <x-icon name="external" class="w-4 h-4" /> <span class="hidden sm:inline">Full view</span>
                            </button>
                            <a href="{{ route('about.view', $selected) }}" target="_blank" class="btn-ghost text-sm" title="Open in new tab">
                                <x-icon name="visit" class="w-4 h-4" /> <span class="hidden sm:inline">Open</span>
                            </a>
                            <a href="{{ route('about.download', $selected) }}" class="btn-primary text-sm" title="Download">
                                <x-icon name="download" class="w-4 h-4" /> <span class="hidden sm:inline">Download</span>
                            </a>
                        </div>
                    </div>

                    <div class="bg-ink-50" wire:key="viewer-{{ $selected->id }}">
                        <iframe src="{{ route('about.view', $selected) }}#toolbar=1&view=FitH"
                                class="w-full h-[70vh] lg:h-[78vh]" title="{{ $selected->title }}"></iframe>
                    </div>

                    <div class="lg:hidden px-4 py-3 border-t border-ink-100 text-center">
                        <p class="text-xs text-ink-700/60">Can't see the document?
                            <a href="{{ route('about.view', $selected) }}" target="_blank" class="text-primary font-medium">Open it here</a>.
                        </p>
                    </div>

                    {{-- ===== Full-view overlay (presenting mode) — both roles ===== --}}
                    <div x-show="full" x-cloak x-ref="fullview"
                         class="fixed inset-0 z-[80] bg-ink-950 flex flex-col" style="display:none;">
                        <div class="flex items-center justify-between gap-3 px-4 py-3 bg-ink-900 text-white">
                            <p class="font-semibold truncate">{{ $selected->title }}</p>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button"
                                        @click="$refs.fullview.requestFullscreen ? $refs.fullview.requestFullscreen().catch(()=>{}) : null"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 hover:bg-white/10">
                                    <x-icon name="external" class="w-4 h-4" /> <span class="hidden sm:inline">Fullscreen</span>
                                </button>
                                <a href="{{ route('about.download', $selected) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 hover:bg-white/10">
                                    <x-icon name="download" class="w-4 h-4" /> <span class="hidden sm:inline">Download</span>
                                </a>
                                <button type="button" @click="full = false; document.fullscreenElement && document.exitFullscreen()"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-white/10 text-white hover:bg-white/20">
                                    <x-icon name="close" class="w-4 h-4" /> Close
                                </button>
                            </div>
                        </div>
                        <template x-if="full">
                            <iframe src="{{ route('about.view', $selected) }}#toolbar=1&view=FitH"
                                    class="flex-1 w-full bg-ink-900" title="{{ $selected->title }} — full view"></iframe>
                        </template>
                    </div>
                @else
                    <div class="grid place-items-center h-[60vh] text-ink-700/50 text-sm">Select a document to view.</div>
                @endif
            </div>
        </div>
    @endif

    {{-- ============ Upload modal — plain POST form (no Livewire AJAX upload) ============ --}}
    @if ($isAdmin)
        <div x-show="uploadOpen" x-cloak @keydown.escape.window="uploadOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
            <div x-show="uploadOpen" x-transition.opacity class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" @click="uploadOpen = false"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div x-show="uploadOpen"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative w-full max-w-lg bg-surface rounded-t-3xl sm:rounded-2xl shadow-pop">

                    <form method="POST" action="{{ route('about.store') }}" enctype="multipart/form-data"
                          x-data="{ submitting: false }" @submit="submitting = true" class="p-6">
                        @csrf
                        <h2 class="text-lg font-bold text-ink-900">Upload company PDF</h2>
                        <p class="text-sm text-ink-700/60 mt-1">Visible to your whole team in About Us.</p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="label">Title</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="input" placeholder="e.g. Company Profile 2026" required />
                                @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Description <span class="text-ink-700/40">(optional)</span></label>
                                <textarea name="description" rows="2" class="input" placeholder="What's in this document?">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">PDF file (max 20 MB)</label>
                                <input type="file" name="file" accept="application/pdf" required
                                       class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                                @error('file') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="uploadOpen = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="submitting">
                                <span x-show="!submitting">Upload</span>
                                <span x-show="submitting" x-cloak>Uploading…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
