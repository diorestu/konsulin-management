<?php

namespace Tests\Feature;

use App\Models\WebsiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_uses_published_content(): void
    {
        WebsiteContent::create([
            'key' => 'hero', 'label' => 'Homepage Hero', 'type' => 'hero',
            'title' => 'Custom Konsulin headline', 'body' => 'Custom public description',
            'is_published' => true, 'sort_order' => 1,
        ]);

        $this->get('/')->assertOk()->assertSee('Custom Konsulin headline')->assertSee('Custom public description');
    }

    public function test_admin_can_update_website_content(): void
    {
        $content = WebsiteContent::create(['key' => 'hero', 'label' => 'Homepage Hero', 'type' => 'hero', 'is_published' => true]);

        $this->get('/website-content')->assertOk()->assertSee('Homepage Hero');
        $this->put(route('website-content.update', $content), [
            'title' => 'Updated headline', 'body' => 'Updated body', 'is_published' => '1',
        ])->assertRedirect(route('website-content.index'));

        $this->assertDatabaseHas('website_contents', ['id' => $content->id, 'title' => 'Updated headline', 'body' => 'Updated body']);
    }
}
