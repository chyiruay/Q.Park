<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\myCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The admin area and every destructive action used to be open: no auth, no role,
 * and deletes behind plain GET links. These lock that down.
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function adminUrls(): array
    {
        return [
            '/admin' => ['/admin'],
            '/viewUser' => ['/viewUser'],
            '/viewProduct' => ['/viewProduct'],
            '/viewCategory' => ['/viewCategory'],
            '/allOrder' => ['/allOrder'],
            '/viewFeedback' => ['/viewFeedback'],
            '/salesReport' => ['/salesReport'],
            '/insertProduct' => ['/insertProduct'],
            '/insertCategory' => ['/insertCategory'],
        ];
    }

    #[DataProvider('adminUrls')]
    public function test_guests_are_sent_to_login(string $url)
    {
        $this->get($url)->assertRedirect('/login');
    }

    #[DataProvider('adminUrls')]
    public function test_ordinary_customers_are_forbidden(string $url)
    {
        $this->actingAs(User::factory()->create())->get($url)->assertStatus(403);
    }

    public function test_admins_are_bounced_out_of_the_customer_area()
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $urls = ['/', '/help', '/products', '/viewDetail/'.$product->categoryID,
                 '/home', '/order/'.$product->id, '/myCart', '/myOrder', '/insertFeedback'];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertRedirect(route('admin'), 302, $url);
        }

        $this->actingAs($admin)->post('/product', ['keyword' => 'hotel'])
            ->assertRedirect(route('admin'));
    }

    public function test_admins_keep_their_own_profile_pages()
    {
        $admin = User::factory()->admin()->create();

        foreach (['/viewProfile', '/editProfile/'.$admin->id, '/editPassword/'.$admin->id] as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200, $url);
        }
    }

    public function test_customers_still_reach_the_shopfront()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        foreach (['/', '/help', '/products', '/home', '/order/'.$product->id, '/myCart'] as $url) {
            $this->actingAs($customer)->get($url)->assertStatus(200, $url);
        }
    }

    public function test_guests_still_reach_the_public_shopfront()
    {
        foreach (['/', '/help', '/products'] as $url) {
            $this->get($url)->assertStatus(200, $url);
        }
    }

    public function test_signed_in_admin_visiting_login_lands_on_the_dashboard()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/login')
            ->assertRedirect(route('admin'));
    }

    public function test_customer_pages_are_closed_to_guests()
    {
        foreach (['/myCart', '/myOrder', '/viewProfile'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $this->post('/deleteAccount')->assertRedirect('/login');
    }

    public function test_a_guest_clicking_a_car_park_is_told_why_they_landed_on_login()
    {
        $product = Product::factory()->create();

        $this->get('/order/'.$product->id)
            ->assertRedirect('/login')
            ->assertSessionHas('notice');

        $this->get('/login')->assertSee('you need an account to book a parking space', false);
    }

    public function test_logging_in_returns_a_guest_to_the_car_park_they_clicked()
    {
        $product = Product::factory()->create();
        $customer = User::factory()->create();

        $this->get('/order/'.$product->id)->assertRedirect('/login');

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect('/order/'.$product->id);
    }

    public function test_deletes_are_not_reachable_by_get()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/deleteProduct/'.$product->id)
            ->assertStatus(405);

        $this->assertModelExists($product);
    }

    public function test_customer_cannot_delete_a_product()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/deleteProduct/'.$product->id)
            ->assertStatus(403);

        $this->assertModelExists($product);
    }

    public function test_admin_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post('/deleteProduct/'.$product->id)
            ->assertRedirect(route('viewProduct'));

        $this->assertModelMissing($product);
    }

    public function test_deleting_a_missing_product_is_a_404_not_a_500()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/deleteProduct/999')
            ->assertStatus(404);
    }

    public function test_category_with_car_parks_cannot_be_deleted()
    {
        $product = Product::factory()->create();
        $category = Category::findOrFail($product->categoryID);

        $this->actingAs(User::factory()->admin()->create())
            ->post('/deleteCategory/'.$category->id)
            ->assertSessionHasErrors();

        $this->assertModelExists($category);
    }

    public function test_account_delete_only_removes_your_own_account()
    {
        $victim = User::factory()->create();
        $attacker = User::factory()->create();

        $this->actingAs($attacker)->post('/deleteAccount', ['id' => $victim->id]);

        $this->assertModelExists($victim);
        $this->assertModelMissing($attacker);
    }

    public function test_cannot_delete_another_users_cart_item()
    {
        $owner = User::factory()->create();
        $item = myCart::create([
            'orderID' => '',
            'userID' => $owner->id,
            'date' => now()->addDay()->toDateString(),
            'startTime' => '10:00',
            'space' => 'A3',
            'quantity' => 2,
            'productID' => Product::factory()->create()->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/deleteItem/'.$item->id)
            ->assertStatus(404);

        $this->assertModelExists($item);
    }
}
