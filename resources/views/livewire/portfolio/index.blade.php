<div x-data="portfolioForm({
        tab: @js($tab),
        storeUrl: @js(route('portfolio.store')),
        updateBase: @js(url('portfolio')),
        hasErrors: @js($errors->any() && $isAdmin),
        old: @js(['type' => old('type'), 'title' => old('title'), 'description' => old('description'), 'url' => old('url'), 'credentials' => old('credentials', [])]),
     })">

    <x-page-header title="Portfolio" subtitle="Show clients the work — websites, video ads, graphics and automations.">
        @if ($isAdmin)
            <x-slot:actions>
                <button type="button" @click="create(@js($tab))" class="btn-primary">
                    <x-icon name="plus" class="w-4 h-4" /> Add to this section
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-ink-100 mb-6 overflow-x-auto no-scrollbar">
        @foreach ($types as $key => $label)
            @php $icon = ['website' => 'globe', 'video' => 'play', 'graphic' => 'image', 'automation' => 'bolt'][$key]; @endphp
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
        <x-empty-state :icon="['website' => 'globe', 'video' => 'play', 'graphic' => 'image', 'automation' => 'bolt'][$tab]"
            title="Nothing here yet"
            message="{{ $isAdmin ? 'Add your first item so the team can show it to clients.' : 'Your manager hasn\'t added anything to this section yet.' }}">
            @if ($isAdmin)
                <x-slot:action>
                    <button type="button" @click="create(@js($tab))" class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add item</button>
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

    {{-- ===================== Admin add/edit modal ===================== --}}
    @if ($isAdmin)
        @php
            $typeLabels = ['website' => 'Websites & Software', 'video' => 'Video Ads', 'graphic' => 'Graphics', 'automation' => 'Automations'];
        @endphp
        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div x-show="open" x-transition class="relative w-full max-w-xl bg-surface rounded-t-3xl sm:rounded-2xl shadow-pop">
                    <form :action="action" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true" class="p-6 max-h-[88vh] overflow-y-auto">
                        @csrf
                        <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                        <input type="hidden" name="type" :value="formType">

                        <h2 class="text-lg font-bold text-ink-900" x-text="editId ? 'Edit item' : 'Add to ' + ({{ Illuminate\Support\Js::from($typeLabels) }})[formType]"></h2>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="label">Title</label>
                                <input type="text" name="title" x-model="fields.title" class="input" placeholder="e.g. Acme Corporate Website" required />
                                @error('title') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- URL (website/video/graphic) --}}
                            <div x-show="formType !== 'automation'">
                                <label class="label">
                                    <span x-text="({website:'Live site URL', video:'Instagram reel URL', graphic:'Instagram link (optional)', automation:''})[formType]"></span>
                                </label>
                                <input type="url" name="url" x-model="fields.url" class="input"
                                       :required="formType === 'website' || formType === 'video'"
                                       :placeholder="({website:'https://example.com', video:'https://www.instagram.com/reel/...', graphic:'https://www.instagram.com/p/...', automation:''})[formType]" />
                                @error('url') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Image (graphics only — automations add images in their gallery) --}}
                            <div x-show="formType === 'graphic'">
                                <label class="label">
                                    Image
                                    <span class="text-ink-700/40" x-show="editId">(leave empty to keep current)</span>
                                    <span class="text-ink-700/40" x-show="!editId">— or paste an Instagram link above</span>
                                </label>
                                <input type="file" name="image" accept="image/*"
                                       class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                                @error('image') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <p x-show="formType === 'automation'" class="text-xs text-ink-700/50">After you add the automation, open it to upload its images.</p>

                            <div>
                                <label class="label">Description <span class="text-ink-700/40">(optional)</span></label>
                                <textarea name="description" x-model="fields.description" rows="2" class="input" placeholder="A short note about this work"></textarea>
                            </div>

                            {{-- Credentials (website only) --}}
                            <div x-show="formType === 'website'">
                                <div class="flex items-center justify-between">
                                    <label class="label mb-0">Demo logins <span class="text-ink-700/40">(per user type)</span></label>
                                    <button type="button" @click="addCred()" class="text-sm font-medium text-primary">+ Add login</button>
                                </div>
                                <div class="space-y-2 mt-2">
                                    <template x-for="(cred, i) in fields.credentials" :key="i">
                                        <div class="rounded-xl border border-ink-100 p-3 space-y-2 relative">
                                            <button type="button" @click="removeCred(i)" class="absolute top-2 right-2 text-ink-700/40 hover:text-danger"><x-icon name="close" class="w-4 h-4" /></button>
                                            <input type="text" :name="'credentials['+i+'][label]'" x-model="cred.label" class="input text-sm" placeholder="User type (e.g. Admin, Manager, Customer)" />
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" :name="'credentials['+i+'][username]'" x-model="cred.username" class="input text-sm" placeholder="Username / email" />
                                                <input type="text" :name="'credentials['+i+'][password]'" x-model="cred.password" class="input text-sm" placeholder="Password" />
                                            </div>
                                            <input type="url" :name="'credentials['+i+'][url]'" x-model="cred.url" class="input text-sm" placeholder="Login page URL (optional)" />
                                        </div>
                                    </template>
                                    <p x-show="fields.credentials.length === 0" class="text-xs text-ink-700/50">No logins added. Click “Add login” to include demo credentials.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="open = false" class="btn-ghost">Cancel</button>
                            <button type="submit" class="btn-primary" :disabled="submitting">
                                <span x-show="!submitting" x-text="editId ? 'Save changes' : 'Add item'"></span>
                                <span x-show="submitting" x-cloak>Saving…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== Automation gallery (server-rendered, opens via ?open=) ===================== --}}
    @if ($openItem)
        <div class="fixed inset-0 z-[55] overflow-y-auto" x-data="{ lb: null }">
            <div class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm" wire:click="closeGallery"></div>
            <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div class="relative w-full max-w-3xl bg-surface rounded-t-3xl sm:rounded-2xl shadow-pop max-h-[90vh] flex flex-col">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3 p-5 border-b border-ink-100">
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-ink-900 truncate">{{ $openItem->title }}</h2>
                            @if ($openItem->description)<p class="text-sm text-ink-700/60 mt-0.5">{{ $openItem->description }}</p>@endif
                            <p class="text-xs text-ink-700/50 mt-1">{{ $openItem->images->count() }} {{ Str::plural('image', $openItem->images->count()) }}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @if ($isAdmin)
                                <button type="button" @click="edit({{ \Illuminate\Support\Js::from($openItem->only(['id','type','title','description','url','credentials'])) }})"
                                        class="grid place-items-center h-9 w-9 rounded-lg text-ink-700/60 hover:bg-ink-50" title="Rename"><x-icon name="edit" class="w-4 h-4" /></button>
                                <button type="button" wire:click="delete({{ $openItem->id }})" wire:confirm="Delete this automation and all its images?"
                                        class="grid place-items-center h-9 w-9 rounded-lg text-ink-700/60 hover:bg-danger/10 hover:text-danger" title="Delete"><x-icon name="trash" class="w-4 h-4" /></button>
                            @endif
                            <button type="button" wire:click="closeGallery" class="grid place-items-center h-9 w-9 rounded-lg text-ink-700/60 hover:bg-ink-50" title="Close"><x-icon name="close" class="w-5 h-5" /></button>
                        </div>
                    </div>

                    {{-- Images --}}
                    <div class="p-5 overflow-y-auto">
                        @if ($openItem->images->isEmpty())
                            <div class="text-center py-10 text-ink-700/50 text-sm">No images yet.{{ $isAdmin ? ' Add some below.' : '' }}</div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($openItem->images as $img)
                                    <div class="relative group rounded-xl overflow-hidden bg-ink-50 aspect-square">
                                        <button type="button" @click="lb = @js($img->imageUrl())" class="block w-full h-full">
                                            <img src="{{ $img->imageUrl() }}" alt="" loading="lazy" class="w-full h-full object-cover" />
                                        </button>
                                        @if ($isAdmin)
                                            <form method="POST" action="{{ route('portfolio.images.destroy', $img) }}" class="absolute top-1.5 right-1.5" onsubmit="return confirm('Remove this image?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="grid place-items-center h-8 w-8 rounded-lg bg-ink-950/60 text-white opacity-0 group-hover:opacity-100 hover:bg-danger transition"><x-icon name="trash" class="w-4 h-4" /></button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Admin: add images (plain POST upload) --}}
                    @if ($isAdmin)
                        <div class="p-5 border-t border-ink-100">
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
                                <button type="submit" class="btn-primary" :disabled="submitting">
                                    <span x-show="!submitting"><x-icon name="upload" class="w-4 h-4" /> Upload</span>
                                    <span x-show="submitting" x-cloak>Uploading…</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="lb" x-cloak @click="lb = null" @keydown.escape.window="lb = null"
                         class="fixed inset-0 z-[70] bg-ink-950/85 backdrop-blur grid place-items-center p-4" style="display:none;">
                        <img :src="lb" class="max-w-full max-h-[90vh] rounded-xl shadow-pop" alt="preview" />
                        <button @click.stop="lb = null" class="absolute top-4 right-4 text-white/70 hover:text-white"><x-icon name="close" class="w-7 h-7" /></button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
