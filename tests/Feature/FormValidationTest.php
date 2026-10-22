<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * These forms had no server-side rules, so an empty or hostile submission threw
 * an unhandled exception — a 500 that leaks source and SQL when APP_DEBUG is on.
 */
class FormValidationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_product_without_an_image_is_rejected_not_fatal()
    {
        $this->actingAs($this->admin())
            ->post('/insertProduct/store', [
                'productName' => 'Test Parking',
                'price' => 4.50,
                'categoryID' => Category::factory()->create()->id,
            ])
            ->assertSessionHasErrors(['product-image', 'product-floorPlan']);

        $this->assertSame(0, Product::count());
    }

    public function test_empty_product_form_is_rejected()
    {
        $this->actingAs($this->admin())
            ->post('/insertProduct/store', [])
            ->assertSessionHasErrors(['productName', 'price', 'categoryID', 'product-image']);
    }

    public function test_non_image_upload_is_rejected()
    {
        $this->actingAs($this->admin())
            ->post('/insertProduct/store', [
                'productName' => 'Test Parking',
                'price' => 4.50,
                'categoryID' => Category::factory()->create()->id,
                'product-image' => UploadedFile::fake()->create('shell.php', 8, 'application/x-php'),
                'product-floorPlan' => UploadedFile::fake()->image('plan.jpg'),
            ])
            ->assertSessionHasErrors('product-image');

        $this->assertSame(0, Product::count());
    }

    public function test_upload_gets_a_server_generated_filename()
    {
        $this->actingAs($this->admin())->post('/insertProduct/store', [
            'productName' => 'Test Parking',
            'price' => 4.50,
            'categoryID' => Category::factory()->create()->id,
            'product-image' => UploadedFile::fake()->image('amari.jpg'),
            'product-floorPlan' => UploadedFile::fake()->image('plan.jpg'),
        ])->assertRedirect(route('viewProduct'));

        $product = Product::firstOrFail();
        $this->assertNotSame('amari.jpg', $product->image, 'client filename must not be reused');
        $this->assertStringEndsWith('.jpg', $product->image);

        @unlink(public_path('images/'.$product->image));
        @unlink(public_path('floorPlan/'.$product->floorPlan));
    }

    public function test_decimal_price_is_accepted()
    {
        $product = Product::factory()->create(['price' => 4.50]);

        $this->actingAs($this->admin())->post('/updateProduct', [
            'hid' => $product->id,
            'productName' => $product->name,
            'price' => 3.50,
            'categoryID' => $product->categoryID,
        ])->assertRedirect(route('viewProduct'));

        $this->assertEquals(3.50, $product->fresh()->price);
    }

    public function test_empty_feedback_is_rejected_not_fatal()
    {
        $this->actingAs(User::factory()->create())
            ->post('/insertFeedback/store', [])
            ->assertSessionHasErrors('feedbackContent');

        $this->assertSame(0, Feedback::count());
    }

    public function test_feedback_is_stored()
    {
        $this->actingAs(User::factory()->create())
            ->post('/insertFeedback/store', ['feedbackContent' => 'More bays please'])
            ->assertRedirect(route('insertFeedback'));

        $this->assertSame('More bays please', Feedback::firstOrFail()->content);
    }

    public function test_booking_rejects_a_space_that_does_not_exist()
    {
        $this->actingAs(User::factory()->create())
            ->post('/addCart', [
                'id' => Product::factory()->create()->id,
                'date' => now()->addDay()->toDateString(),
                'time' => '10:00',
                'quantity' => 2,
                'space' => 'ZZ99',
            ])
            ->assertSessionHasErrors('space');
    }
}
