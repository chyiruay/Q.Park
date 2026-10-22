<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\myCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Covers the add-to-cart validation that replaced a 500 on empty fields,
 * and that the booking date/time actually survive the round trip.
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_booking_is_rejected_not_fatal()
    {
        $this->actingAs(User::factory()->create())
            ->post('/addCart', [])
            ->assertSessionHasErrors(['id', 'date', 'time', 'quantity', 'space']);

        $this->assertSame(0, myCart::count());
    }

    public function test_past_date_is_rejected()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/addCart', [
                'id' => $product->id,
                'date' => now()->subDay()->toDateString(),
                'time' => '10:00',
                'quantity' => 2,
                'space' => 'A3',
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_a_start_time_that_has_already_passed_today_is_rejected()
    {
        $this->travelTo(today()->setTime(12, 0));

        $this->actingAs(User::factory()->create())
            ->post('/addCart', [
                'id' => Product::factory()->create()->id,
                'date' => today()->toDateString(),
                'time' => '00:30',
                'quantity' => 2,
                'space' => 'A3',
            ])
            ->assertSessionHasErrors('time');

        $this->assertSame(0, myCart::count());
    }

    /** @return array<string, array{string, int, bool}> */
    public static function overlaps(): array
    {
        // an existing booking holds A1 from 10:00 for 2 hours
        return [
            'same slot' => ['10:00', 2, true],
            'starts inside' => ['11:00', 1, true],
            'ends inside' => ['09:00', 2, true],
            'swallows it' => ['09:00', 4, true],
            'ends exactly as it starts' => ['08:00', 2, false],
            'starts exactly as it ends' => ['12:00', 2, false],
            'later that day' => ['15:00', 1, false],
        ];
    }

    #[DataProvider('overlaps')]
    public function test_double_booking_a_space_is_refused(string $time, int $hours, bool $clashes)
    {
        $product = Product::factory()->create();
        $date = now()->addDays(3)->toDateString();
        $user = User::factory()->create();

        myCart::create([
            'orderID' => '', 'userID' => $user->id, 'date' => $date,
            'startTime' => '10:00', 'space' => 'A1', 'quantity' => 2,
            'productID' => $product->id,
        ]);

        $response = $this->actingAs($user)->post('/addCart', [
            'id' => $product->id,
            'date' => $date,
            'time' => $time,
            'quantity' => $hours,
            'space' => 'A1',
        ]);

        if ($clashes) {
            $response->assertSessionHasErrors('space');
            $this->assertSame(1, myCart::count());
        } else {
            $response->assertSessionHasNoErrors();
            $this->assertSame(2, myCart::count());
        }
    }

    public function test_a_different_space_or_car_park_does_not_clash()
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $date = now()->addDays(3)->toDateString();
        $user = User::factory()->create();

        myCart::create([
            'orderID' => '', 'userID' => $user->id, 'date' => $date,
            'startTime' => '10:00', 'space' => 'A1', 'quantity' => 2,
            'productID' => $product->id,
        ]);

        $booking = ['date' => $date, 'time' => '10:00', 'quantity' => 2];

        $this->actingAs($user)->post('/addCart', $booking + ['id' => $product->id, 'space' => 'A2'])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/addCart', $booking + ['id' => $other->id, 'space' => 'A1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, myCart::count());
    }

    public function test_a_booking_running_past_midnight_still_clashes()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $date = now()->addDays(3)->toDateString();

        // 23:00 for 3 hours, so it occupies 01:00 the next day
        myCart::create([
            'orderID' => '', 'userID' => $user->id, 'date' => $date,
            'startTime' => '23:00', 'space' => 'A1', 'quantity' => 3,
            'productID' => $product->id,
        ]);

        $this->actingAs($user)->post('/addCart', [
            'id' => $product->id,
            'date' => now()->addDays(4)->toDateString(),
            'time' => '01:00',
            'quantity' => 1,
            'space' => 'A1',
        ])->assertSessionHasErrors('space');

        $this->assertSame(1, myCart::count());
    }

    public function test_valid_booking_stores_date_and_start_time()
    {
        $product = Product::factory()->create(['price' => 6.00]);
        $user = User::factory()->create();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($user)->post('/addCart', [
            'id' => $product->id,
            'date' => $date,
            'time' => '10:00',
            'quantity' => 2,
            'space' => 'A3',
        ])->assertRedirect(route('myCart'));

        $cart = myCart::firstOrFail();
        $this->assertSame($date, $cart->date);
        $this->assertSame('10:00', substr($cart->startTime, 0, 5));
        $this->assertSame('A3', $cart->space);

        $this->actingAs($user)->get('/myCart')
            ->assertStatus(200)
            ->assertSee('A3')
            ->assertSee('10:00');
    }
}
