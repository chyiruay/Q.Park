<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests: every main page loads without crashing.
 *
 * These assert status codes only, not correctness. They exist to catch
 * breakage during the Laravel 10 -> 13 upgrade, where the failure mode is
 * a 500 from a renamed method or a removed Blade directive.
 *
 * Who may reach what is AccessControlTest's job, not this file's.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load()
    {
        $product = Product::factory()->create();

        foreach (['/', '/help', '/login', '/register', '/products', '/viewDetail/'.$product->categoryID] as $url) {
            $this->get($url)->assertStatus(200, $url);
        }
    }

    public function test_search_handles_a_hostile_keyword()
    {
        $this->post('/product', ['keyword' => "' OR 1=1 -- <b>x</b>"])->assertStatus(200);
    }

    public function test_customer_pages_load()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $urls = [
            '/home',
            '/order/'.$product->id,
            '/myCart',
            '/myOrder',
            '/insertFeedback',
            '/viewProfile',
            '/editProfile/'.$customer->id,
            '/editPassword/'.$customer->id,
        ];

        foreach ($urls as $url) {
            $this->actingAs($customer)->get($url)->assertStatus(200, $url);
        }
    }

    public function test_admin_sees_the_admin_nav_on_shared_profile_pages()
    {
        $admin = User::factory()->admin()->create();

        foreach (['/viewProfile', '/editProfile/'.$admin->id, '/editPassword/'.$admin->id] as $url) {
            $this->actingAs($admin)->get($url)
                ->assertStatus(200, $url)
                ->assertSee('bg_admin.jpg')
                ->assertSee(route('viewCategory'));
        }
    }

    public function test_admin_pages_load()
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $category = Category::findOrFail($product->categoryID);

        $urls = [
            '/admin',
            '/viewUser',
            '/viewProduct',
            '/insertProduct',
            '/editProduct/'.$product->id,
            '/viewCategory',
            '/insertCategory',
            '/editCategory/'.$category->id,
            '/viewFeedback',
            '/allOrder',
            '/salesReport',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200, $url);
        }
    }
}
