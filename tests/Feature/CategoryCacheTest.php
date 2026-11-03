<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Category::cached() is only safe while every write invalidates it, and a missed
 * invalidation fails silently — an admin adds a category and it never shows up in
 * the car park form. These assert the invalidation, not the caching.
 */
class CategoryCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_returns_every_category()
    {
        Category::factory()->count(3)->create();

        $this->assertCount(3, Category::cached());
    }

    public function test_cached_is_served_from_the_cache()
    {
        Category::factory()->create(['name' => 'Mall']);
        Category::cached();

        //a raw insert bypasses the model events, so a cache hit must not see it
        \DB::table('categories')->insert(['name' => 'Smuggled']);

        $this->assertCount(1, Category::cached());
    }

    public function test_creating_a_category_invalidates_the_cache()
    {
        Category::cached();

        Category::create(['name' => 'Airport']);

        $this->assertCount(1, Category::cached());
    }

    public function test_renaming_a_category_invalidates_the_cache()
    {
        $category = Category::factory()->create(['name' => 'Old']);
        Category::cached();

        $category->name = 'New';
        $category->save();

        $this->assertSame('New', Category::cached()->first()->name);
    }

    public function test_deleting_a_category_invalidates_the_cache()
    {
        $category = Category::factory()->create();
        Category::cached();

        $category->delete();

        $this->assertCount(0, Category::cached());
    }

    public function test_the_car_park_form_dropdown_reflects_a_new_category()
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Existing']);

        //warm the cache through the real page, add a category through the real form,
        //then check the dropdown picked it up rather than serving a stale list
        $this->actingAs($admin)->get('/insertProduct')->assertSee('Existing');

        $this->actingAs($admin)->post('/insertCategory/store', [
            'categoryName' => 'Stadium',
        ])->assertRedirect(route('viewCategory'));

        $this->actingAs($admin)->get('/insertProduct')->assertSee('Stadium');
    }

    public function test_deleting_a_category_with_car_parks_is_refused_and_leaves_the_cache_intact()
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Occupied']);
        Product::factory()->create(['categoryID' => (string) $category->id]);

        Category::cached();

        $this->actingAs($admin)
            ->post("/deleteCategory/{$category->id}")
            ->assertRedirect(route('viewCategory'));

        $this->assertCount(1, Category::cached());
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('categories.all');
    }
}
