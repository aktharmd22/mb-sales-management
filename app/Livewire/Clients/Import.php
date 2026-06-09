<?php

namespace App\Livewire\Clients;

use App\Exports\ImportTemplateExport;
use App\Imports\VisitsImport;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Import data')]
#[Layout('layouts.app')]
class Import extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $assignTo = null;

    public ?array $result = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->assignTo = User::where('role', User::ROLE_SALESPERSON)->value('id');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ImportTemplateExport, 'malayznbeat-import-template.xlsx');
    }

    public function import(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'assignTo' => ['required', 'exists:users,id'],
        ]);

        $import = new VisitsImport($this->assignTo, auth()->id());
        $import->import($this->file->getRealPath());

        $this->result = [
            'clients' => $import->clientsCreated,
            'visits' => $import->visitsCreated,
            'skipped' => $import->skipped,
            'errors' => array_slice($import->errors, 0, 8),
        ];

        $this->reset('file');
        $this->dispatch('toast', message: "Imported {$import->visitsCreated} visits.", type: 'success');
    }

    public function render()
    {
        return view('livewire.clients.import', [
            'salespeople' => User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
