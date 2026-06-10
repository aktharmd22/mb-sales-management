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

    public function test_admin_can_add_an_article_link_with_fetched_preview(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response('<html><head><meta property="og:image" content="https://cdn.example.com/cover.jpg"></head></html>', 200),
        ]);

        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), [
                'type' => 'article',
                'title' => 'How we 3x-ed bookings',
                'url' => 'https://blog.example.com/case-study',
            ])
            ->assertRedirect(route('portfolio.index', ['tab' => 'article']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('portfolio_items', [
            'type' => 'article',
            'title' => 'How we 3x-ed bookings',
            'preview_image' => 'https://cdn.example.com/cover.jpg',
        ]);
    }

    public function test_article_requires_a_url(): void
    {
        \Illuminate\Support\Facades\Http::fake();

        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), ['type' => 'article', 'title' => 'No link'])
            ->assertSessionHasErrors('url');
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

    public function test_add_and_edit_modal_open_close_is_server_driven(): void
    {
        $admin = $this->admin();
        $item = PortfolioItem::create(['type' => 'website', 'title' => 'Existing', 'is_active' => true, 'uploaded_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Index::class, ['tab' => 'website'])
            ->assertSet('showForm', false)
            ->call('startCreate', 'website')
            ->assertSet('showForm', true)->assertSet('formType', 'website')->assertSet('editId', null)
            ->call('startEdit', $item->id)
            ->assertSet('showForm', true)->assertSet('editId', $item->id)->assertSet('fTitle', 'Existing')
            ->call('closeForm')
            ->assertSet('showForm', false);
    }

    public function test_salesperson_cannot_open_create_modal(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        Livewire::actingAs($sales)
            ->test(Index::class)
            ->call('startCreate', 'website')
            ->assertForbidden();
    }

    public function test_admin_can_create_automation_without_image(): void
    {
        $this->actingAs($this->admin())
            ->post(route('portfolio.store'), ['type' => 'automation', 'title' => 'Onboarding Flow'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('portfolio_items', ['type' => 'automation', 'title' => 'Onboarding Flow']);
    }

    public function test_admin_can_add_multiple_images_to_automation(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $item = PortfolioItem::create(['type' => 'automation', 'title' => 'Flow', 'is_active' => true, 'uploaded_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('portfolio.images.store', $item), ['images' => [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
            ]])
            ->assertRedirect();

        $this->assertSame(2, $item->images()->count());
        Storage::disk('public')->assertExists($item->images()->first()->image_path);
    }

    public function test_admin_can_delete_an_automation_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $item = PortfolioItem::create(['type' => 'automation', 'title' => 'Flow', 'is_active' => true, 'uploaded_by' => $admin->id]);
        $img = $item->images()->create(['image_path' => UploadedFile::fake()->image('a.png')->store('portfolio', 'public')]);

        $this->actingAs($admin)
            ->delete(route('portfolio.images.destroy', $img))
            ->assertRedirect();

        $this->assertSame(0, $item->images()->count());
    }

    public function test_salesperson_cannot_add_images(): void
    {
        $admin = $this->admin();
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $item = PortfolioItem::create(['type' => 'automation', 'title' => 'Flow', 'is_active' => true, 'uploaded_by' => $admin->id]);

        $this->actingAs($sales)
            ->post(route('portfolio.images.store', $item), ['images' => [UploadedFile::fake()->image('a.png')]])
            ->assertForbidden();
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
