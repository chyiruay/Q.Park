<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests: every main page loads without crashing.
 *
 * These assert status codes only, not correctness. They exist to catch
 * breakage during the Laravel 10 -> 13 upgrade, where the failure mode is
 * a 500 from a renamed method or a removed Blade directive.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /** Pages any visitor can reach. */
    public function test_welcome_page_loads()
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_help_page_loads()
    {
        $this->get('/help')->assertStatus(200);
    }

    public function test_products_page_loads()
    {
        $this->get('/products')->assertStatus(200);
    }

    public function test_view_category_page_loads()
    {
        $this->get('/viewCategory')->assertStatus(200);
    }

    public function test_login_page_loads()
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_register_page_loads()
    {
        $this->get('/register')->assertStatus(200);
    }

    /**
     * NOTE: /admin has no auth middleware, so a guest reaches it.
     * This asserts current behaviour, not desired behaviour.
     */
    public function test_admin_page_loads()
    {
        $this->get('/admin')->assertStatus(200);
    }

    /** Pages behind the 'auth' middleware. */
    public function test_home_redirects_guests_to_login()
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_home_loads_for_authenticated_user()
    {
        $this->actingAs(User::factory()->create())
             ->get('/home')
             ->assertStatus(200);
    }

    public function test_my_cart_loads_for_authenticated_user()
    {
        $this->actingAs(User::factory()->create())
             ->get('/myCart')
             ->assertStatus(200);
    }

    public function test_my_order_loads_for_authenticated_user()
    {
        $this->actingAs(User::factory()->create())
             ->get('/myOrder')
             ->assertStatus(200);
    }
}
