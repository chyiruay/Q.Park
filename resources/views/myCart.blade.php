@extends('layout')
@section('content')
<script>
    function cal(){
        var names = document.getElementsByName('subtotal[]');
        var subtotal=0;
        var cboxes = document.getElementsByName('cid[]');
        for(var i=0; i<cboxes.length; i++){
            if(cboxes[i].checked){  //calculate if checked
                subtotal+=parseFloat(names[i].value);
            }
        }
        document.querySelectorAll('.cart-sub').forEach(function (el) {
            el.textContent=subtotal.toFixed(2);
        });
    }
    document.addEventListener('DOMContentLoaded', cal);  //items start ticked, so show their total straight away
</script>

<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>My Cart</h1>
        <p>{{ $carts->count() }} {{ Str::plural('booking', $carts->count()) }} waiting for payment.</p>
    </div>

    @include('partials.alerts')

    <div class="row g-4">
        <div class="{{ $carts->isEmpty() ? 'col-12' : 'col-lg-8' }}">
            <div class="admin-panel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><span class="visually-hidden">Pay</span></th>
                                <th scope="col">Car Park</th>
                                <th scope="col">Space</th>
                                <th scope="col">Date &amp; Time</th>
                                <th scope="col">Hours</th>
                                <th scope="col" class="text-end">Subtotal</th>
                                <th scope="col"><span class="visually-hidden">Remove</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($carts as $cart)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="cid[]" form="payment-form" value="{{ $cart->cid }}" onclick="cal()" checked aria-label="Pay for {{ $cart->name }}, space {{ $cart->space }}">
                                        <input type="hidden" name="subtotal[]" value="{{ $cart->price * $cart->cartQty }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ asset('images/' . $cart->image) }}" alt="" class="admin-thumb">
                                            <div>
                                                <div class="fw-semibold">{{ $cart->name }}</div>
                                                <div class="small text-muted">RM {{ number_format($cart->price, 2) }} / hour</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge admin-badge--customer">{{ $cart->space ?? '-' }}</span></td>
                                    <td class="text-nowrap">
                                        {{ $cart->bookDate ? \Carbon\Carbon::parse($cart->bookDate)->format('d M Y') : '-' }}
                                        <div class="small text-muted">{{ $cart->bookTime ? \Carbon\Carbon::parse($cart->bookTime)->format('h:i A') : '' }}</div>
                                    </td>
                                    <td>{{ $cart->cartQty }}</td>
                                    <td class="text-end fw-semibold text-nowrap">RM {{ number_format($cart->price * $cart->cartQty, 2) }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('delete.Item', ['id' => $cart->cid]) }}" onsubmit="return confirm('Remove this booking?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Remove" aria-label="Remove {{ $cart->name }}, space {{ $cart->space }}">
                                                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="admin-panel__empty">
                                        Your cart is empty.
                                        <a href="{{ route('Product') }}" class="d-block mt-2">Browse car parks</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($carts->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end text-muted">Selected total</td>
                                    <td class="text-end fw-bold text-nowrap">RM <span class="cart-sub">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @if ($carts->isNotEmpty())
        <div class="col-lg-4">
            <form action="{{ route('payment.post') }}" method="post" class="require-validation admin-panel" data-cc-on-file="false" data-stripe-publishable-key="{{ config('services.stripe.key') }}" id="payment-form">
                @csrf
                <h2 class="h5 fw-bold mb-3">Card Payment</h2>

                {{--
                    These inputs deliberately have no name attribute: card
                    details must never reach our server. Stripe.js reads them
                    by class and posts only the token it returns.
                --}}
                <div class="mb-3 required">
                    <label class="form-label small fw-semibold" for="card-name">Name on Card</label>
                    <input id="card-name" class="form-control" type="text" autocomplete="cc-name" required>
                </div>
                <div class="mb-3 required">
                    <label class="form-label small fw-semibold" for="card-number">Card Number</label>
                    <input id="card-number" class="form-control card-number" type="text" inputmode="numeric" autocomplete="off" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4 required">
                        <label class="form-label small fw-semibold" for="card-month">Month</label>
                        <input id="card-month" class="form-control card-expiry-month" type="text" inputmode="numeric" placeholder="MM" required>
                    </div>
                    <div class="col-4 required">
                        <label class="form-label small fw-semibold" for="card-year">Year</label>
                        <input id="card-year" class="form-control card-expiry-year" type="text" inputmode="numeric" placeholder="YYYY" required>
                    </div>
                    <div class="col-4 required">
                        <label class="form-label small fw-semibold" for="card-cvc">CVC</label>
                        <input id="card-cvc" class="form-control card-cvc" type="text" inputmode="numeric" autocomplete="off" required>
                    </div>
                </div>

                <div class="error mb-3 d-none">
                    <div class="alert alert-danger mb-0" role="alert">Please correct the errors and try again.</div>
                </div>

                <div class="booking-total">
                    <span>Total</span>
                    <strong>RM <span class="cart-sub">0.00</span></strong>
                </div>

                <button class="btn btn-qpark w-100" type="submit">Pay Now</button>
            </form>
        </div>
        @endif
    </div>
</div>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
// The Vite bundle loads as type="module", which is deferred, so jQuery is not
// defined while this script is parsed. DOMContentLoaded fires after deferred
// scripts run, so waiting for it guarantees window.$ exists.
document.addEventListener('DOMContentLoaded', function () {
  var $form = $(".require-validation");

  function showError(message) {
    $form.find('div.error').removeClass('d-none').find('.alert').text(message);
  }

  $form.on('submit', function(e) {
    var $inputs = $form.find('.required input'),
    valid = true;
    $form.find('div.error').addClass('d-none');
    $form.find('.is-invalid').removeClass('is-invalid');
    $inputs.each(function(i, el) {
        var $input = $(el);
        if ($input.val() === '') {
            $input.addClass('is-invalid');
            valid = false;
        }
    });
    if (!valid) {
        e.preventDefault();
        showError('Please correct the errors and try again.');
        return;
    }
    if (!$('input[name="cid[]"]:checked').length) {
        e.preventDefault();
        showError('Tick the booking you want to pay for.');
        return;
    }
    if (!$form.data('stripe-publishable-key')) {
        e.preventDefault();
        showError('Card payment is not configured yet. Set STRIPE_KEY and STRIPE_SECRET in .env.');
        return;
    }
    if (!$form.data('cc-on-file')) {
      e.preventDefault();
      Stripe.setPublishableKey($form.data('stripe-publishable-key'));
      Stripe.createToken({
          number: $('.card-number').val(),
          cvc: $('.card-cvc').val(),
          exp_month: $('.card-expiry-month').val(),
          exp_year: $('.card-expiry-year').val()
      }, stripeResponseHandler);
    }
  });

  function stripeResponseHandler(status, response) {
      if (response.error) {
          showError(response.error.message);
      } else {
          /* token contains id, last4, and card type */
          var token = response['id'];
          $form.append($('<input type="hidden" name="stripeToken">').val(token));
          $form.get(0).submit();
      }
  }
});
</script>
@endsection
