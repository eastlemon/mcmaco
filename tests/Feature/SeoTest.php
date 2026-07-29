<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_canonical(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $content = $response->content();
        $this->assertStringContainsString('rel="canonical"', $content);
    }

    public function test_home_page_has_organization_and_website_schema(): void
    {
        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringContainsString('"Organization"', $content);
        $this->assertStringContainsString('"WebSite"', $content);
        $this->assertStringContainsString('SearchAction', $content);
    }

    public function test_home_page_has_meta_tags(): void
    {
        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('og:title', $content);
        $this->assertStringContainsString('og:locale', $content);
        $this->assertStringContainsString('ru_RU', $content);
        $this->assertStringContainsString('twitter:card', $content);
    }

    public function test_product_page_has_product_schema(): void
    {
        $ad = Ad::factory()->create(['status' => 'active']);

        $response = $this->get(route('ads.show', $ad->slug));

        $content = $response->content();
        $this->assertStringContainsString('"Product"', $content);
        $this->assertStringContainsString('"BreadcrumbList"', $content);
        $this->assertStringContainsString('rel="canonical"', $content);
    }

    public function test_product_page_has_og_type_product(): void
    {
        $ad = Ad::factory()->create(['status' => 'active']);

        $response = $this->get(route('ads.show', $ad->slug));

        $content = $response->content();
        $this->assertStringContainsString('og:type', $content);
        $this->assertStringContainsString('product', $content);
    }

    public function test_category_page_has_breadcrumb_schema(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $content = $response->content();
        $this->assertStringContainsString('"BreadcrumbList"', $content);
        $this->assertStringContainsString('rel="canonical"', $content);
    }

    public function test_metrika_not_rendered_when_disabled(): void
    {
        config()->set('analytics.metrika.enabled', false);

        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringNotContainsString('mc.yandex.ru/metrika/tag.js', $content);
    }

    public function test_metrika_rendered_when_enabled(): void
    {
        config()->set('analytics.metrika.enabled', true);
        config()->set('analytics.metrika.counter_id', '12345678');

        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringContainsString('mc.yandex.ru/metrika/tag.js', $content);
        $this->assertStringContainsString("ym(12345678", $content);
    }

    public function test_ga4_not_rendered_when_disabled(): void
    {
        config()->set('analytics.ga4.enabled', false);

        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringNotContainsString('googletagmanager.com/gtag/js', $content);
    }

    public function test_ga4_rendered_when_enabled(): void
    {
        config()->set('analytics.ga4.enabled', true);
        config()->set('analytics.ga4.measurement_id', 'G-TESTCODE');

        $response = $this->get('/');

        $content = $response->content();
        $this->assertStringContainsString('googletagmanager.com/gtag/js?id=G-TESTCODE', $content);
        $this->assertStringContainsString("'G-TESTCODE'", $content);
    }

    public function test_robots_txt_exists(): void
    {
        $this->assertFileExists(public_path('robots.txt'));
        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }
}
