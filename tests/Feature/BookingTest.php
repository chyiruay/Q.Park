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

    /** An abandoned cart row stops holding its space once the hold runs out; a paid one never does. */
    public function test_only_an_expired_unpaid_hold_frees_the_space()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $date = now()->addDays(3)->toDateString();
        $row = ['userID' => $user->id, 'date' => $date, 'startTime' => '10:00', 'quantity' => 2, 'productID' => $product->id];
        $booking = ['id' => $product->id, 'date' => $date, 'time' => '10:00', 'quantity' => 2];

        myCart::create($row + ['orderID' => '', 'space' => 'A1']);
        myCart::create($row + ['orderID' => '7', 'space' => 'A2']);

        $this->travel(myCart::HOLD_MINUTES + 1)->minutes();

        $this->actingAs($user)->post('/addCart', $booking + ['space' => 'A1'])->assertSessionHasNoErrors();
        $this->actingAs($user)->post('/addCart', $booking + ['space' => 'A2'])->assertSessionHasErrors('space');

        $this->assertSame(3, myCart::count());
    }

    /** The cart's countdown starts from what the server says is left of the hold. */
    public function test_the_cart_shows_how_long_the_hold_has_left()
    {
        $this->freezeSecond();   // created_at drops microseconds; now() otherwise keeps them
        $user = User::factory()->create();
        myCart::create([
            'orderID' => '', 'userID' => $user->id, 'date' => now()->addDays(3)->toDateString(),
            'startTime' => '10:00', 'space' => 'A1', 'quantity' => 2,
            'productID' => Product::factory()->create()->id,
        ]);

        $this->travel(5)->minutes();
        $this->actingAs($user)->get('/myCart')
            ->assertSee('data-left="'.((myCart::HOLD_MINUTES - 5) * 60).'"', false);

        $this->travel(myCart::HOLD_MINUTES)->minutes();
        $this->actingAs($user)->get('/myCart')->assertSee('data-left="0"', false);
    }

    private function pendingRow(User $user, array $overrides = []): myCart
    {
        return myCart::create($overrides + [
            'orderID' => '', 'userID' => $user->id, 'date' => now()->addDays(3)->toDateString(),
            'startTime' => '10:00', 'space' => 'A1', 'quantity' => 2,
            'productID' => Product::factory()->create()->id,
        ]);
    }

    public function test_a_cart_booking_can_be_edited()
    {
        $user = User::factory()->create();
        $row = $this->pendingRow($user);
        $newDate = now()->addDays(5)->toDateString();

        $this->actingAs($user)->get('/editItem/'.$row->id)
            ->assertOk()
            ->assertSee('value="'.$row->date.'"', false)
            ->assertSee('Save Changes');

        $this->travel(myCart::HOLD_MINUTES + 1)->minutes();

        // 11:00 overlaps the row's own old 10:00-12:00 slot, which must not count as a clash
        $this->actingAs($user)->post('/updateItem/'.$row->id, [
            'date' => $newDate, 'time' => '11:00', 'quantity' => 3, 'space' => 'A2',
            'id' => Product::factory()->create()->id,   // a posted car park is ignored
        ])->assertRedirect(route('myCart'))->assertSessionHasNoErrors();

        $row->refresh();
        $this->assertSame([$newDate, '11:00', 3, 'A2'], [$row->date, substr($row->startTime, 0, 5), (int) $row->quantity, $row->space]);
        $this->assertSame(1, myCart::count());
        $this->assertTrue($row->created_at->gte(now()->subMinute()), 'an edit renews the hold');
    }

    public function test_an_edit_into_someone_elses_slot_is_refused()
    {
        $user = User::factory()->create();
        $row = $this->pendingRow($user);
        $this->pendingRow(User::factory()->create(), ['productID' => $row->productID, 'space' => 'A2']);

        $this->actingAs($user)->post('/updateItem/'.$row->id, [
            'date' => $row->date, 'time' => '10:00', 'quantity' => 2, 'space' => 'A2',
        ])->assertSessionHasErrors('space');

        $this->assertSame('A1', $row->fresh()->space);
    }

    public function test_only_your_own_unpaid_bookings_can_be_edited()
    {
        $owner = User::factory()->create();
        $theirs = $this->pendingRow($owner);
        $paid = $this->pendingRow($owner, ['orderID' => '9', 'space' => 'A5']);
        $form = ['date' => now()->addDays(4)->toDateString(), 'time' => '09:00', 'quantity' => 1, 'space' => 'A9'];

        $this->actingAs(User::factory()->create())->get('/editItem/'.$theirs->id)->assertNotFound();
        $this->post('/updateItem/'.$theirs->id, $form)->assertNotFound();

        $this->actingAs($owner)->get('/editItem/'.$paid->id)->assertNotFound();
        $this->post('/updateItem/'.$paid->id, $form)->assertNotFound();

        $this->assertSame('A1', $theirs->fresh()->space);
        $this->assertSame('A5', $paid->fresh()->space);
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

    public function test_my_orders_lists_the_paid_bookings_of_that_user_only()
    {
        $product = Product::factory()->create(['name' => 'Sunway Pyramid']);
        $user = User::factory()->create();
        $other = User::factory()->create();

        $order = \App\Models\myOrder::create(['paymentStatus' => 'Done', 'userID' => $user->id, 'amount' => 10]);
        myCart::create([
            'orderID' => $order->id, 'userID' => $user->id, 'date' => now()->addDay()->toDateString(),
            'startTime' => '09:00', 'space' => 'B7', 'quantity' => 2, 'productID' => $product->id,
        ]);

        $this->actingAs($user)->get('/myOrder')
            ->assertStatus(200)
            ->assertSee('Sunway Pyramid')
            ->assertSee('B7')
            ->assertSee('RM 10.00');

        $this->actingAs($other)->get('/myOrder')
            ->assertStatus(200)
            ->assertDontSee('Sunway Pyramid');
    }
}
