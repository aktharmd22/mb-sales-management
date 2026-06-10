<div>
    <x-page-header title="Portfolio" subtitle="Show clients the work — websites, video ads, graphics, automations and articles.">
        @if ($isAdmin)
            <x-slot:actions>
                <button type="button" wire:click="startCreate('{{ $tab }}')" class="btn-primary">
                    <x-icon name="plus" class="w-4 h-4" /> Add to this section
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-ink-100 mb-6 overflow-x-auto no-scrollbar">
        @foreach ($types as $key => $label)
            @php $icon = ['website' => 'globe', 'video' => 'play', 'graphic' => 'image', 'automation' => 'bolt', 'article' => 'news'][$key]; @endphp
            <button wire:click="$set('tab', '{{ $key }}')"
                    @class([
                        'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition',
                        'border-primary text-primary' => $tab === $key,
                        'border-transparent text-ink-700/60 hover:text-ink-900' => $tab !== $key,
                    ])>
                <x-icon :name="$icon" class="w-4 h-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <x-empty-state :icon="['website' => 'globe', 'video' => 'play', 'graphic' => 'image', 'automation' => 'bolt', 'article' => 'news'][$tab]"
            title="Nothing here yet"
            message="{{ $isAdmin ? 'Add your first item so the team can show it to clients.' : 'Your manager hasn\'t added anything to this section yet.' }}">
            @if ($isAdmin)
                <x-slot:action>
                    <button type="button" wire:click="startCreate('{{ $tab }}')" class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add item</button>
                </x-slot:action>
            @endif
        </x-empty-state>
    @else
        {{-- ===================== WEBSITES & SOFTWARE ===================== --}}
        @if ($tab === 'website')
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($items as $item)
                    <div class="card p-5 {{ ! $item->is_active ? 'opacity-60' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-bold text-ink-900">{{ $item->title }}</h3>
                                @if ($item->description)<p class="text-sm text-ink-700/70 mt-1">{{ $item->description }}</p>@endif
                            </div>
                            @if ($isAdmin) @include('livewire.portfolio.partials.admin-actions', ['item' => $item]) @endif
                        </div>

                        @if ($item->url)
                            <a href="{{ $item->url }}" target="_blank" rel="noopener" class="btn-primary mt-3 text-sm w-full sm:w-auto">
                                <x-icon name="external" class="w-4 h-4" /> Visit live site
                            </a>
                        @endif

                        @if (! empty($item->credentials))
                            <div class="mt-4 rounded-xl border border-ink-100 overflow-hidden">
                                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-ink-700/50 bg-canvas">Demo logins</p>
                                <div class="divide-y divide-ink-100">
                                    @foreach ($item->credentials as $cred)
                                        <div class="p-3" x-data="{ show: false }">
                                            @if (! empty($cred['label']))<p class="text-xs font-semibold text-primary mb-1.5">{{ $cred['label'] }}</p>@endif
                                            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                                                @if (! empty($cred['username']))
                                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-canvas px-2.5 py-1.5">
                                                        <span class="truncate"><span class="text-ink-700/50">User:</span> {{ $cred['username'] }}</span>
                                                        <button type="button" @click="navigator.clipboard.writeText(@js($cred['username'])); $dispatch('toast', {message:'Username copied', type:'info'})" class="text-ink-700/40 hover:text-primary shrink-0"><x-icon name="copy" class="w-4 h-4" /></button>
                                                    </div>
                                                @endif
                                                @if (! empty($cred['password']))
                                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-canvas px-2.5 py-1.5">
                                                        <span class="truncate font-mono" x-text="show ? @js($cred['password']) : '••••••••'"></span>
                                                        <span class="flex items-center gap-1 shrink-0">
                                                            <button type="button" @click="show = !show" class="text-ink-700/40 hover:text-ink-900" title="Show / hide">
                                                                <x-icon name="eye" class="w-4 h-4" x-show="!show" />
                                                                <x-icon name="eye-off" class="w-4 h-4" x-show="show" x-cloak />
                                                            </button>
                                                            <button type="button" @click="navigator.clipboard.writeText(@js($cred['password'])); $dispatch('toast', {message:'Password copied', type:'info'})" class="text-ink-700/40 hover:text-primary"><x-icon name="copy" class="w-4 h-4" /></button>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            @if (! empty($cred['url']))
                                                <a href="{{ $cred['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs text-primary mt-1.5">Login page <x-icon name="external" class="w-3 h-3" /></a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($isAdmin && ! $item->is_active)<span class="badge bg-ink-100 text-ink-700 mt-3">Hidden</span>@endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ===================== VIDEO ADS ===================== --}}
        @if ($tab === 'video')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    <div class="card overflow-hidden {{ ! $item->is_active ? 'opacity-60' : '' }}">
                        @if ($item->instagramEmbedUrl())
                            <div class="bg-ink-900">
                                <iframe src="{{ $item->instagramEmbedUrl() }}" class="w-full" style="height:560px" frameborder="0" scrolling="no" allowtransparency allowfullscreen loading="lazy"></iframe>
                            </div>
                        @else
                            <a href="{{ $item->url }}" target="_blank" rel="noopener" class="grid place-items-center h-48 bg-ink-900 text-white/70 hover:text-white">
                                <span class="text-center"><x-icon name="play" class="w-10 h-10 mx-auto" /><span class="block text-sm mt-2">Open reel on Instagram</span></span>
                            </a>
                        @endif
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-ink-900 truncate">{{ $item->title }}</h3>
                                    @if ($item->description)<p class="text-sm text-ink-700/70 mt-0.5 line-clamp-2">{{ $item->description }}</p>@endif
                                </div>
                                @if ($isAdmin) @include('livewire.portfolio.partials.admin-actions', ['item' => $item]) @endif
                            </div>
                            <a href="{{ $item->url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm text-primary mt-2"><x-icon name="instagram" class="w-4 h-4" /> Open on Instagram</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ===================== GRAPHICS (single image or Instagram) ===================== --}}
        @if ($tab === 'graphic')
            <div x-data="{ lb: null }">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        <div class="card overflow-hidden group {{ ! $item->is_active ? 'opacity-60' : '' }}">
                            @if ($item->imageUrl())
                                {{-- Uploaded image (click to enlarge) --}}
                                <button type="button" @click="lb = @js($item->imageUrl())" class="block w-full aspect-[4/3] bg-ink-50 overflow-hidden">
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-[1.03] transition" />
                                </button>
                            @elseif ($tab === 'graphic' && $item->instagramEmbedUrl())
                                {{-- No upload — embed the Instagram post instead --}}
                                <div class="bg-ink-900">
                                    <iframe src="{{ $item->instagramEmbedUrl() }}" class="w-full" style="height:480px"
                                            frameborder="0" scrolling="no" allowtransparency allowfullscreen loading="lazy"></iframe>
                                </div>
                            @endif
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-ink-900 truncate">{{ $item->title }}</h3>
                                        @if ($item->description)<p class="text-sm text-ink-700/70 mt-0.5 line-clamp-2">{{ $item->description }}</p>@endif
                                    </div>
                                    @if ($isAdmin) @include('livewire.portfolio.partials.admin-actions', ['item' => $item]) @endif
                                </div>
                                @if ($item->url)
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm text-primary mt-2"><x-icon name="instagram" class="w-4 h-4" /> View on Instagram</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Lightbox --}}
                <div x-show="lb" x-cloak @click="lb = null" @keydown.escape.window="lb = null"
                     class="fixed inset-0 z-[60] bg-ink-950/80 backdrop-blur grid place-items-center p-4" style="display:none;">
                    <img :src="lb" class="max-w-full max-h-[90vh] rounded-xl shadow-pop" alt="preview" />
                    <button @click="lb = null" class="absolute top-4 right-4 text-white/70 hover:text-white"><x-icon name="close" class="w-7 h-7" /></button>
                </div>
            </div>
        @endif

        {{-- ===================== AUTOMATIONS (one automation → many images) ===================== --}}
        @if ($tab === 'automation')
            @if ($openItem)
                {{-- ===== Full-page gallery for one automation ===== --}}
                <div x-data="{
                        images: @js($openItem->images->map->imageUrl()->values()),
                        lb: false,
                        i: 0,
                        openAt(n) { this.i = n; this.lb = true; },
                        next() { if (this.images.length) this.i = (this.i + 1) % this.images.length; },
                        prev() { if (this.images.length) this.i = (this.i - 1 + this.images.length) % this.images.length; },
                     }">
                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                        <div class="flex items-center gap-3 min-w-0">
                            <button wire:click="closeGallery" class="btn-ghost shrink-0"><x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back</button>
                            <div class="min-w-0">
                                <h2 class="text-xl font-bold text-ink-900 truncate">{{ $openItem->title }}</h2>
                                <p class="text-sm text-ink-700/60">{{ $openItem->images->count() }} {{ Str::plural('image', $openItem->images->count()) }}@if ($openItem->description) · {{ $openItem->description }}@endif</p>
                            </div>
                        </div>
                        @if ($isAdmin)
                            <div class="flex items-center gap-2 shrink-0">
                                <button wire:click="startEdit({{ $openItem->id }})" class="btn-ghost text-sm"><x-icon name="edit" class="w-4 h-4" /> Rename</button>
                                <button wire:click="delete({{ $openItem->id }})" wire:confirm="Delete this automation and all its images?" class="btn text-sm bg-danger/10 text-danger"><x-icon name="trash" class="w-4 h-4" /> Delete</button>
                            </div>
                        @endif
                    </div>

                    {{-- 2-per-row full images --}}
                    @if ($openItem->images->isEmpty())
                        <div class="card text-center py-14 text-ink-700/50 text-sm">No images yet.{{ $isAdmin ? ' Add some below.' : '' }}</div>
                    @else
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach ($openItem->images as $idx => $img)
                                <div class="relative group card overflow-hidden">
                                    <button type="button" @click="openAt({{ $idx }})" class="block w-full">
                                        <img src="{{ $img->imageUrl() }}" alt="" loading="lazy" class="w-full h-auto object-contain bg-ink-50" />
                                    </button>
                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('portfolio.images.destroy', $img) }}" class="absolute top-2 right-2" onsubmit="return confirm('Remove this image?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="grid place-items-center h-9 w-9 rounded-lg bg-ink-950/60 text-white opacity-0 group-hover:opacity-100 hover:bg-danger transition"><x-icon name="trash" class="w-4 h-4" /></button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Admin: add images --}}
                    @if ($isAdmin)
                        <div class="card p-5 mt-4">
                            <form method="POST" action="{{ route('portfolio.images.store', $openItem) }}" enctype="multipart/form-data"
                                  x-data="{ submitting: false }" @submit="submitting = true" class="flex flex-col sm:flex-row sm:items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label class="label">Add images <span class="text-ink-700/40">(select one or many — max 8 MB each)</span></label>
                                    <input type="file" name="images[]" accept="image/*" multiple required
                                           class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                                    @error('images') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    @error('images.*') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" :disabled="submitting"
                                        class="shrink-0 inline-flex flex-col items-center justify-center gap-1 rounded-xl bg-primary text-white px-6 py-2.5 text-sm font-semibold leading-none shadow-sm hover:bg-primary-600 transition active:scale-[0.98] disabled:opacity-70">
                                    <x-icon name="upload" class="w-5 h-5" />
                                    <span x-show="!submitting">Upload</span>
                                    <span x-show="submitting" x-cloak>Uploading…</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Lightbox with prev / next --}}
                    <div x-show="lb" x-cloak
                         @keydown.escape.window="lb = false" @keydown.arrow-right.window="next()" @keydown.arrow-left.window="prev()"
                         class="fixed inset-0 z-[70] bg-ink-950/90 backdrop-blur flex items-center justify-center p-4 sm:p-10" style="display:none;">
                        <button @click="lb = false" class="absolute top-4 right-4 text-white/70 hover:text-white"><x-icon name="close" class="w-7 h-7" /></button>
                        <button @click="prev()" x-show="images.length > 1" class="absolute left-3 sm:left-6 grid place-items-center h-12 w-12 rounded-full bg-white/10 text-white hover:bg-white/20"><x-icon name="chevron-right" class="w-6 h-6 rotate-180" /></button>
                        <img :src="images[i]" class="max-w-full max-h-[85vh] rounded-xl shadow-pop" alt="preview" />
                        <button @click="next()" x-show="images.length > 1" class="absolute right-3 sm:right-6 grid place-items-center h-12 w-12 rounded-full bg-white/10 text-white hover:bg-white/20"><x-icon name="chevron-right" class="w-6 h-6" /></button>
                        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 text-sm text-white/70 tnum" x-text="(i + 1) + ' / ' + images.length"></div>
                    </div>
                </div>
            @else
                {{-- ===== Automation cards ===== --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        @php $cover = $item->images->first(); $count = $item->images->count(); @endphp
                        <button type="button" wire:click="openGallery({{ $item->id }})"
                                class="card overflow-hidden group text-left hover:shadow-card-hover transition {{ ! $item->is_active ? 'opacity-60' : '' }}">
                            <div class="relative aspect-[4/3] bg-ink-50 overflow-hidden">
                                @if ($cover)
                                    <img src="{{ $cover->imageUrl() }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-[1.03] transition" />
                                @else
                                    <div class="grid place-items-center h-full text-ink-700/30"><x-icon name="bolt" class="w-10 h-10" /></div>
                                @endif
                                <span class="absolute bottom-2 right-2 badge bg-ink-900/75 text-white">
                                    <x-icon name="image" class="w-3 h-3" /> {{ $count }}
                                </span>
                            </div>
                            <div class="p-4 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-ink-900 truncate">{{ $item->title }}</h3>
                                    @if ($item->description)<p class="text-sm text-ink-700/70 mt-0.5 line-clamp-2">{{ $item->description }}</p>@endif
                                    @if ($isAdmin && ! $item->is_active)<span class="badge bg-ink-100 text-ink-700 mt-1.5">Hidden</span>@endif
                                </div>
                                <span class="text-primary text-sm font-medium shrink-0 mt-0.5">Open →</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- ===================== ARTICLES (external links with preview) ===================== --}}
        @if ($tab === 'article')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    <div class="card overflow-hidden flex flex-col group {{ ! $item->is_active ? 'opacity-60' : '' }}">
                        {{-- Thumbnail (uploaded image or fetched link preview) --}}
                        <a href="{{ $item->url }}" target="_blank" rel="noopener" class="relative block aspect-[16/9] bg-ink-50 overflow-hidden">
                            @if ($item->thumbnail())
                                <img src="{{ $item->thumbnail() }}" alt="{{ $item->title }}" loading="lazy" referrerpolicy="no-referrer"
                                     class="w-full h-full object-cover group-hover:scale-[1.03] transition"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';" />
                                <span class="hidden absolute inset-0 place-items-center text-ink-700/30"><x-icon name="news" class="w-10 h-10" /></span>
                            @else
                                <span class="grid place-items-center h-full text-ink-700/30"><x-icon name="news" class="w-10 h-10" /></span>
                            @endif
                        </a>

                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-semibold text-ink-900 line-clamp-2">{{ $item->title }}</h3>
                                @if ($isAdmin) @include('livewire.portfolio.partials.admin-actions', ['item' => $item]) @endif
                            </div>
                            @if ($item->description)<p class="text-sm text-ink-700/70 mt-1 line-clamp-2 flex-1">{{ $item->description }}</p>@else<div class="flex-1"></div>@endif
                            <div class="flex items-center justify-between gap-2 mt-3">
                                <a href="{{ $item->url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:text-primary-600">
                                    Read article <x-icon name="external" class="w-4 h-4" />
                                </a>
                                @if ($isAdmin && ! $item->is_active)<span class="badge bg-ink-100 text-ink-700">Hidden</span>@endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- ===================== Admin add/edit modal (Livewire-driven visibility) ===================== --}}
    @php
        $typeLabels = ['website' => 'Websites & Software', 'video' => 'Video Ads', 'graphic' => 'Graphics', 'automation' => 'Automations', 'article' => 'Articles'];
        $urlLabels = ['website' => 'Live site URL', 'video' => 'Instagram reel URL', 'graphic' => 'Instagram link (optional)', 'article' => 'Article link'];
        $urlPlaceholders = ['website' => 'https://example.com', 'video' => 'https://www.instagram.com/reel/...', 'graphic' => 'https://www.instagram.com/p/...', 'article' => 'https://example.com/blog/your-article'];
        $eid = $editId ?: old('edit_id');
        $ft = ($errors->any() && old('type')) ? old('type') : $formType;
        $modalOpen = $showForm || $errors->any();
    @endphp
    @if ($isAdmin && $modalOpen && array_key_exists($ft, $typeLabels))
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" wire:click="closeForm"></div>
            <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div wire:key="pform-{{ $eid ?: 'new' }}-{{ $ft }}"
                     x-data="{ creds: @js(old('credentials', $fCredentials)), submitting: false }"
                     @keydown.escape.window="$wire.closeForm()"
                     class="relative w-full max-w-xl bg-surface rounded-t-3xl sm:rounded-2xl shadow-pop animate-fade-in-up">
                    <form method="POST" enctype="multipart/form-data"
                          action="{{ $eid ? route('portfolio.update', $eid) : route('portfolio.store') }}"
                          @submit="submitting = true" class="p-6 max-h-[88vh] overflow-y-auto">
                        @csrf
                        @if ($eid) @method('PUT') @endif
                        <input type="hidden" name="edit_id" value="{{ $eid }}">
                        <input type="hidden" name="type" value="{{ $ft }}">

                        <h2 class="text-lg font-bold text-ink-900">{{ $eid ? 'Edit item' : 'Add to ' . $typeLabels[$ft] }}</h2>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="label">Title</label>
                                <input type="text" name="title" value="{{ old('title', $fTitle) }}" class="input" placeholder="e.g. Acme Corporate Website" required autofocus />
                                @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            @if ($ft !== 'automation')
                                <div>
                                    <label class="label">{{ $urlLabels[$ft] ?? 'URL' }}</label>
                                    <input type="url" name="url" value="{{ old('url', $fUrl) }}" class="input"
                                           @if (in_array($ft, ['website', 'video', 'article'])) required @endif
                                           placeholder="{{ $urlPlaceholders[$ft] ?? '' }}" />
                                    @error('url') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if (in_array($ft, ['graphic', 'article']))
                                <div>
                                    <label class="label">
                                        {{ $ft === 'article' ? 'Thumbnail' : 'Image' }}
                                        @if ($ft === 'article')
                                            <span class="text-ink-700/40">{{ $eid ? '(leave empty to keep current)' : '(optional — auto-fetched from the link if left blank)' }}</span>
                                        @else
                                            <span class="text-ink-700/40">{{ $eid ? '(leave empty to keep current)' : '— or paste an Instagram link above' }}</span>
                                        @endif
                                    </label>
                                    <input type="file" name="image" accept="image/*"
                                           class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                                    @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if ($ft === 'automation')
                                <p class="text-xs text-ink-700/50">After you add the automation, open it to upload its images.</p>
                            @endif

                            <div>
                                <label class="label">Description <span class="text-ink-700/40">(optional)</span></label>
                                <textarea name="description" rows="2" class="input" placeholder="A short note about this work">{{ old('description', $fDescription) }}</textarea>
                            </div>

                            @if ($ft === 'website')
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label class="label mb-0">Demo logins <span class="text-ink-700/40">(per user type)</span></label>
                                        <button type="button" @click="creds.push({label:'',username:'',password:'',url:''})" class="text-sm font-medium text-primary">+ Add login</button>
                                    </div>
                                    <div class="space-y-2 mt-2">
                                        <template x-for="(cred, i) in creds" :key="i">
                                            <div class="rounded-xl border border-ink-100 p-3 space-y-2 relative">
                                                <button type="button" @click="creds.splice(i, 1)" class="absolute top-2 right-2 text-ink-700/40 hover:text-danger"><x-icon name="close" class="w-4 h-4" /></button>
                                                <input type="text" :name="'credentials['+i+'][label]'" x-model="cred.label" class="input text-sm" placeholder="User type (e.g. Admin, Manager, Customer)" />
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" :name="'credentials['+i+'][username]'" x-model="cred.username" class="input text-sm" placeholder="Username / email" />
                                                    <input type="text" :name="'credentials['+i+'][password]'" x-model="cred.password" class="input text-sm" placeholder="Password" />
                                                </div>
                                                <input type="url" :name="'credentials['+i+'][url]'" x-model="cred.url" class="input text-sm" placeholder="Login page URL (optional)" />
                                            </div>
                                        </template>
                                        <p x-show="creds.length === 0" class="text-xs text-ink-700/50">No logins added. Click “Add login” to include demo credentials.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeForm" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="submitting">
                                <span x-show="!submitting">{{ $eid ? 'Save changes' : 'Add item' }}</span>
                                <span x-show="submitting" x-cloak>Saving…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
