<?php

namespace App\Livewire\Company;

use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('About Us')]
#[Layout('layouts.app')]
class AboutUs extends Component
{
    public ?int $selectedId = null;

    public function mount(): void
    {
        $this->selectedId = $this->documentsQuery()->value('id');
    }

    private function documentsQuery()
    {
        return CompanyDocument::query()
            ->when(! auth()->user()->isAdmin(), fn ($q) => $q->active())
            ->orderBy('sort_order')
            ->latest('id');
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $doc = CompanyDocument::findOrFail($id);
        $doc->update(['is_active' => ! $doc->is_active]);
        $this->dispatch('toast', message: $doc->is_active ? 'Now visible to the team.' : 'Hidden from the team.', type: 'info');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $doc = CompanyDocument::findOrFail($id);
        Storage::disk('local')->delete($doc->file_path);
        $doc->delete();

        if ($this->selectedId === $id) {
            $this->selectedId = $this->documentsQuery()->value('id');
        }
        $this->dispatch('toast', message: 'Document removed.', type: 'success');
    }

    public function render()
    {
        $documents = $this->documentsQuery()->get();
        $selected = $documents->firstWhere('id', $this->selectedId) ?? $documents->first();

        return view('livewire.company.about-us', [
            'documents' => $documents,
            'selected' => $selected,
            'isAdmin' => auth()->user()->isAdmin(),
        ]);
    }
}
