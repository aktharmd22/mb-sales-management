<?php

namespace Tests\Feature;

use App\Livewire\Company\AboutUs;
use App\Models\CompanyDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AboutUsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);
    }

    public function test_admin_can_upload_a_pdf(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN])->assignRole('admin');

        $pdf = UploadedFile::fake()->create('brochure.pdf', 4000, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('about.store'), ['title' => 'Company Brochure', 'file' => $pdf])
            ->assertRedirect(route('about.index'))
            ->assertSessionHas('flash');

        $this->assertDatabaseHas('company_documents', [
            'title' => 'Company Brochure',
            'file_name' => 'brochure.pdf',
        ]);

        Storage::disk('local')->assertExists(CompanyDocument::first()->file_path);
    }

    public function test_non_pdf_is_rejected(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN])->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('about.store'), ['title' => 'Nope', 'file' => UploadedFile::fake()->image('photo.jpg')])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, CompanyDocument::count());
    }

    public function test_salesperson_cannot_upload(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        $this->actingAs($sales)
            ->post(route('about.store'), ['title' => 'x', 'file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertForbidden();
    }

    public function test_salesperson_only_sees_active_documents(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN])->assignRole('admin');
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        CompanyDocument::create(['title' => 'Visible', 'file_path' => 'company/a.pdf', 'file_name' => 'a.pdf', 'file_size' => 100, 'uploaded_by' => $admin->id, 'is_active' => true]);
        CompanyDocument::create(['title' => 'Hidden', 'file_path' => 'company/b.pdf', 'file_name' => 'b.pdf', 'file_size' => 100, 'uploaded_by' => $admin->id, 'is_active' => false]);

        Livewire::actingAs($sales)
            ->test(AboutUs::class)
            ->assertSee('Visible')
            ->assertDontSee('Hidden');
    }
}
