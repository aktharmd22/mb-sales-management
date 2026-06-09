<?php

use App\Livewire\Actions\Logout;
use App\Livewire\Admin\Targets\Index as TargetsIndex;
use App\Livewire\Admin\Team\Index as TeamIndex;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\PortfolioController;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientsShow;
use App\Livewire\Company\AboutUs;
use App\Livewire\Dashboard;
use App\Livewire\FollowUps\Index as FollowUpsIndex;
use App\Livewire\Pipeline\Board as PipelineBoard;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Visits\LogVisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Clients
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientsShow::class)->name('clients.show');

    // Log a visit (optionally pre-selecting a client via ?client=ID)
    Route::get('log-visit', LogVisit::class)->name('visits.create');

    // Follow-ups
    Route::get('follow-ups', FollowUpsIndex::class)->name('followups.index');

    // Pipeline funnel + kanban
    Route::get('pipeline', PipelineBoard::class)->name('pipeline');

    // Reports
    Route::get('reports', ReportsIndex::class)->name('reports');

    // Portfolio — showcase work to pitch clients (viewable by everyone; admins manage)
    Route::get('portfolio', \App\Livewire\Portfolio\Index::class)->name('portfolio.index');

    // About Us — company documents (viewable by everyone; admins manage)
    Route::get('about', AboutUs::class)->name('about.index');
    Route::get('about/{document}/view', [CompanyDocumentController::class, 'view'])->name('about.view');
    Route::get('about/{document}/download', [CompanyDocumentController::class, 'view'])
        ->defaults('mode', 'download')->name('about.download');

    // Profile (Breeze)
    Route::view('profile', 'profile')->name('profile');

    // Logout
    Route::post('logout', function (Logout $logout) {
        $logout();

        return redirect('/');
    })->name('logout');

    // ---------------- Admin only ----------------
    Route::middleware('role:admin')->group(function () {
        Route::get('team', TeamIndex::class)->name('team.index');
        Route::get('targets', TargetsIndex::class)->name('targets.index');
        Route::get('clients-import', \App\Livewire\Clients\Import::class)->name('clients.import');
        Route::post('about', [CompanyDocumentController::class, 'store'])->name('about.store');

        // Portfolio management
        Route::post('portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
        Route::put('portfolio/{item}', [PortfolioController::class, 'update'])->name('portfolio.update');
        Route::delete('portfolio/{item}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');
    });
});

require __DIR__.'/auth.php';
