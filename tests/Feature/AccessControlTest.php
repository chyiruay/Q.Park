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

    public function test_customer_pages_are_closed_to_guests()
    {
        foreach (['/myCart', '/myOrder', '/viewProfile', '/deleteAccount'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
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
