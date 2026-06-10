<?php

namespace App\Livewire\Portfolio;

use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Portfolio')]
#[Layout('layouts.app')]
class Index extends Component
{
    #[Url(history: true)]
    public string $tab = 'website';

    /** Automation whose image gallery is open (null = none). */
    #[Url(history: true)]
    public ?int $open = null;

    public function mount(): void
    {
        if (! array_key_exists($this->tab, PortfolioItem::TYPES)) {
            $this->tab = 'website';
        }
    }

    public function updatedTab(): void
    {
        $this->open = null;
    }

    public function openGallery(int $id): void
    {
        $this->open = $id;
    }

    public function closeGallery(): void
    {
        $this->open = null;
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $item = PortfolioItem::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
        $this->dispatch('toast', message: $item->is_active ? 'Now visible to the team.' : 'Hidden from the team.', type: 'info');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $item = PortfolioItem::findOrFail($id);
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
        $this->dispatch('toast', message: 'Item removed.', type: 'success');
    }

    public function render()
    {
        $isAdmin = auth()->user()->isAdmin();

        $items = PortfolioItem::query()
            ->type($this->tab)
            ->when(! $isAdmin, fn ($q) => $q->active())
            ->when($this->tab === 'automation', fn ($q) => $q->with('images'))
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        // The automation whose gallery is open (only on the automation tab).
        $openItem = null;
        if ($this->tab === 'automation' && $this->open) {
            $openItem = PortfolioItem::with('images')
                ->type('automation')
                ->when(! $isAdmin, fn ($q) => $q->active())
                ->find($this->open);
        }

        return view('livewire.portfolio.index', [
            'items' => $items,
            'openItem' => $openItem,
            'types' => PortfolioItem::TYPES,
            'isAdmin' => $isAdmin,
        ]);
    }
}
