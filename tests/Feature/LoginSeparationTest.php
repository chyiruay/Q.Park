<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /login and /admin/login accept different kinds of account. The split is done
 * in LoginController::credentials(), which adds isAdmin to the lookup based on
 * the posted path — so neither form can be made to accept the other's accounts.
 */
class LoginSeparationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'password';

    public function test_both_login_pages_load()
    {
        $this->get('/login')->assertStatus(200)->assertSee('Login as admin');
        $this->get('/admin/login')->assertStatus(200)->assertSee('Back to customer login');
    }

    public function test_customer_logs_in_on_the_customer_page()
    {
        $customer = User::factory()->create();

        $this->post('/login', ['email' => $customer->email, 'password' => self::PASSWORD])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_logs_in_on_the_admin_page_and_lands_on_the_dashboard()
    {
        $admin = User::factory()->admin()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('admin'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_cannot_use_the_customer_page()
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_cannot_use_the_admin_page()
    {
        $customer = User::factory()->create();

        $this->post('/admin/login', ['email' => $customer->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
