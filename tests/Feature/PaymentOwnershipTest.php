<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\myCart;
use App\Models\myOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

/**
 * A Checkout session id travels in the URL bar, browser history and screenshots.
 * Holding a genuinely paid one must not be enough to claim the booking: the cart
 * rows still have to belong to whoever is signed in. Stripe is stubbed here so a
 * real "paid" session can be replayed by the wrong account.
 */
class PaymentOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function stubStripeAsPaid(string $cids, int $amount = 1100): void
    {
        ApiRequestor::setHttpClient(new class($cids, $amount) implements ClientInterface {
            public function __construct(private string $cids, private int $amount) {}

            public function request($method, $absUrl, $headers, $params, $hasFile)
            {
                return [json_encode([
                    'id' => 'cs_test_stubbed',
                    'object' => 'checkout.session',
                    'payment_status' => 'paid',
                    'amount_total' => $this->amount,
                    'metadata' => ['cids' => $this->cids],
                ]), 200, []];
            }
        });
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);
        parent::tearDown();
    }

    private function cartRow(User $user): myCart
    {
        $product = Product::factory()->create(['price' => 5.50]);

        return myCart::create([
            'orderID' => '', 'userID' => (string) $user->id, 'date' => '2026-12-01',
            'startTime' => '09:00', 'space' => 'A3', 'quantity' => 2,
            'productID' => (string) $product->id,
        ]);
    }

    public function test_the_owner_can_complete_a_paid_session()
    {
        config(['services.stripe.secret' => 'sk_test_stub']);
        $owner = User::factory()->create();
        $row = $this->cartRow($owner);
        $this->stubStripeAsPaid((string) $row->id);

        $this->actingAs($owner)->get('/checkout/return?session_id=cs_test_stubbed')
            ->assertRedirect(route('myOrder'));

        $this->assertSame(1, myOrder::count());
        $order = myOrder::firstOrFail();
        $this->assertSame('paid', $order->paymentStatus);
        $this->assertEquals(11.00, $order->amount);
        $this->assertSame((string) $order->id, $row->fresh()->orderID);
    }

    public function test_another_customer_cannot_claim_a_paid_session()
    {
        config(['services.stripe.secret' => 'sk_test_stub']);
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $row = $this->cartRow($owner);
        $this->stubStripeAsPaid((string) $row->id);

        $this->actingAs($attacker)->get('/checkout/return?session_id=cs_test_stubbed')
            ->assertRedirect(route('myOrder'));

        $this->assertSame(0, myOrder::count(), 'the wrong account must not get an order');
        $this->assertSame('', $row->fresh()->orderID, "the owner's booking must stay unpaid");
    }

    public function test_replaying_a_paid_session_does_not_charge_twice()
    {
        config(['services.stripe.secret' => 'sk_test_stub']);
        $owner = User::factory()->create();
        $row = $this->cartRow($owner);
        $this->stubStripeAsPaid((string) $row->id);

        $this->actingAs($owner)->get('/checkout/return?session_id=cs_test_stubbed');
        $this->actingAs($owner)->get('/checkout/return?session_id=cs_test_stubbed');
        $this->actingAs($owner)->get('/checkout/return?session_id=cs_test_stubbed');

        $this->assertSame(1, myOrder::count(), 'a refresh must not create a second order');
    }

    public function test_an_unpaid_session_records_nothing()
    {
        config(['services.stripe.secret' => 'sk_test_stub']);
        $owner = User::factory()->create();
        $row = $this->cartRow($owner);

        ApiRequestor::setHttpClient(new class implements ClientInterface {
            public function request($method, $absUrl, $headers, $params, $hasFile)
            {
                return [json_encode([
                    'id' => 'cs_test_unpaid', 'object' => 'checkout.session',
                    'payment_status' => 'unpaid', 'amount_total' => 1100,
                    'metadata' => ['cids' => '1'],
                ]), 200, []];
            }
        });

        $this->actingAs($owner)->get('/checkout/return?session_id=cs_test_unpaid')
            ->assertRedirect(route('myCart'));

        $this->assertSame(0, myOrder::count());
        $this->assertSame('', $row->fresh()->orderID);
    }
}
