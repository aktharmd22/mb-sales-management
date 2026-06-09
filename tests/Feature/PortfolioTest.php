<?php

namespace Tests\Feature;

use App\Livewire\Portfolio\Index;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN])->assignRole('admin');
    }

    public function test_admin_can_add_a_website_with_credentials(): void
    {
        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), [
                'type' => 'website',
                'title' => 'Acme Portal',
                'url' => 'https://acme.test',
                'credentials' => [
                    ['label' => 'Admin', 'username' => 'admin@acme.test', 'password' => 'secret', 'url' => ''],
                    ['label' => '', 'username' => '', 'password' => '', 'url' => ''], // empty -> dropped
                ],
            ])
            ->assertRedirect(route('portfolio.index', ['tab' => 'website']));

        $item = PortfolioItem::first();
        $this->assertSame('website', $item->type);
        $this->assertCount(1, $item->credentials);
        $this->assertSame('Admin', $item->credentials[0]['label']);
    }

    public function test_admin_can_add_a_video_ad(): void
    {
        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), [
                'type' => 'video',
                'title' => 'Launch Reel',
                'url' => 'https://www.instagram.com/reel/ABC123/',
            ])
            ->assertRedirect();

        $item = PortfolioItem::first();
        $this->assertSame('https://www.instagram.com/reel/ABC123/embed', $item->instagramEmbedUrl());
    }

    public function test_admin_can_upload_a_graphic_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), [
                'type' => 'graphic',
                'title' => 'Brand Poster',
                'url' => 'https://www.instagram.com/p/XYZ/',
                'image' => UploadedFile::fake()->image('poster.jpg', 800, 600),
            ])
            ->assertRedirect();

        $item = PortfolioItem::first();
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }

    public function test_graphic_requires_image_or_instagram_url(): void
    {
        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), ['type' => 'graphic', 'title' => 'No media'])
            ->assertSessionHasErrors('image');
    }

    public function test_graphic_accepts_instagram_url_without_image(): void
    {
        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), [
                'type' => 'graphic',
                'title' => 'IG only graphic',
                'url' => 'https://www.instagram.com/p/ABC123/',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('portfolio_items', ['title' => 'IG only graphic', 'type' => 'graphic']);
    }

    public function test_salesperson_cannot_manage_portfolio(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        $this->actingAs($sales)
            ->post(route('portfolio.store'), ['type' => 'automation', 'title' => 'x', 'image' => UploadedFile::fake()->image('a.jpg')])
            ->assertForbidden();
    }

    public function test_salesperson_sees_only_active_items_in_tab(): void
    {
        $admin = $this->admin();
        PortfolioItem::create(['type' => 'website', 'title' => 'Live site', 'is_active' => true, 'uploaded_by' => $admin->id]);
        PortfolioItem::create(['type' => 'website', 'title' => 'Draft site', 'is_active' => false, 'uploaded_by' => $admin->id]);
        PortfolioItem::create(['type' => 'video', 'title' => 'Some reel', 'is_active' => true, 'uploaded_by' => $admin->id]);

        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        Livewire::actingAs($sales)
            ->test(Index::class, ['tab' => 'website'])
            ->assertSee('Live site')
            ->assertDontSee('Draft site')
            ->assertDontSee('Some reel'); // different tab
    }
}
