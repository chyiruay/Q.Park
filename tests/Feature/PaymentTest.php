<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\myCart;
use App\Models\myOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Checkout hands the card step to Stripe's hosted page and records the order
 * only after asking Stripe whether the money actually moved. These assert the
 * paths that must hold without any Stripe keys present — above all, that
 * nothing can conjure a paid order by visiting the return URL.
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function cartRow(User $user): myCart
    {
        $product = Product::factory()->create(['price' => 5.50]);

        return myCart::create([
            'orderID' => '', 'userID' => (string) $user->id, 'date' => '2026-12-01',
            'startTime' => '09:00', 'space' => 'A3', 'quantity' => 2,
            'productID' => (string) $product->id,
        ]);
    }

    public function test_guests_cannot_start_checkout()
    {
        $this->post('/checkout', ['cid' => [1]])->assertRedirect('/login');
        $this->get('/checkout/return?session_id=cs_test_x')->assertRedirect('/login');
    }

    public function test_checkout_reports_missing_stripe_keys_instead_of_failing_silently()
    {
        config(['services.stripe.secret' => null]);
        $user = User::factory()->create();
        $row = $this->cartRow($user);

        $this->actingAs($user)->post('/checkout', ['cid' => [$row->id]])
            ->assertSessionHasErrors();

        $this->assertSame(0, myOrder::count());
        $this->assertSame('', $row->fresh()->orderID);
    }

    public function test_checkout_refuses_another_customers_cart_rows()
    {
        $mine = User::factory()->create();
        $theirs = User::factory()->create();
        $theirRow = $this->cartRow($theirs);

        $this->actingAs($mine)->post('/checkout', ['cid' => [$theirRow->id]])
            ->assertSessionHasErrors();

        $this->assertSame(0, myOrder::count());
        $this->assertSame('', $theirRow->fresh()->orderID, 'another customer\'s booking must stay unpaid');
    }

    public function test_checkout_needs_at_least_one_ticked_booking()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/checkout', [])->assertSessionHasErrors('cid');

        $this->assertSame(0, myOrder::count());
    }

    /** The whole point of the redirect flow: arriving at the return URL proves nothing. */
    public function test_the_return_url_cannot_be_forged_into_a_paid_order()
    {
        $user = User::factory()->create();
        $row = $this->cartRow($user);

        // no session id at all
        $this->actingAs($user)->get('/checkout/return')->assertSessionHasErrors('session_id');

        // a made-up session id, with and without keys configured
        foreach ([null, 'sk_test_not_a_real_key'] as $secret) {
            config(['services.stripe.secret' => $secret]);
            $this->actingAs($user)->get('/checkout/return?session_id=cs_test_forged')
                ->assertRedirect(route('myCart'));
        }

        $this->assertSame(0, myOrder::count(), 'no order may exist without a verified payment');
        $this->assertSame('', $row->fresh()->orderID, 'the booking must still be unpaid');
    }

    /** POSTs a checkout.session.completed event, signed the way Stripe signs it. */
    private function webhook(myCart $row, User $user, string $secret = 'whsec_test')
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $payload = json_encode([
            'id' => 'evt_test', 'object' => 'event', 'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'object' => 'checkout.session', 'id' => 'cs_test', 'payment_status' => 'paid',
                'amount_total' => 1100, 'metadata' => ['cids' => (string) $row->id, 'uid' => (string) $user->id],
            ]],
        ]);
        $t = time();
        $sig = "t=$t,v1=".hash_hmac('sha256', "$t.$payload", $secret);

        return $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig], $payload);
    }

    /** Closing the tab after paying must not lose the order. */
    public function test_a_signed_webhook_records_the_order_once()
    {
        $user = User::factory()->create();
        $row = $this->cartRow($user);

        $this->webhook($row, $user)->assertOk();
        $this->webhook($row, $user)->assertOk();   // Stripe redelivers; must not record twice

        $this->assertSame(1, myOrder::count());
        $order = myOrder::first();
        $this->assertEquals(11, $order->amount);
        $this->assertSame((string) $order->id, (string) $row->fresh()->orderID);
    }

    public function test_an_unsigned_webhook_records_nothing()
    {
        $user = User::factory()->create();
        $row = $this->cartRow($user);

        $this->webhook($row, $user, 'whsec_wrong')->assertStatus(400);

        $this->assertSame(0, myOrder::count());
        $this->assertSame('', $row->fresh()->orderID);
    }

    /** Card data must never enter this app's own HTML. */
    public function test_the_cart_page_collects_no_card_details()
    {
        $user = User::factory()->create();
        $this->cartRow($user);

        $html = $this->actingAs($user)->get('/myCart')->content();

        foreach (['card-number', 'card-cvc', 'card-expiry', 'js.stripe.com/v2', 'stripeToken'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "$needle must not appear in our own page");
        }
    }
}
