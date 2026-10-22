@extends('layout')
@section('content')
<script>
    function cal(){
        var names = document.getElementsByName('subtotal[]');
        var subtotal=0;
        var tax=0;
        var total=0;
        var cboxes = document.getElementsByName('cid[]');
        var len = cboxes.length;
        for(var i=0; i<len; i++){
            if(cboxes[i].checked){  //calculate if checked
                subtotal=parseFloat(names[i].value)+parseFloat(subtotal);
            }
        }
        document.getElementById('sub').textContent=subtotal.toFixed(2);
    }
    document.addEventListener('DOMContentLoaded', cal);  //items start ticked, so show their total straight away
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

@include('partials.alerts')

<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <br></br>
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-8" style="color:white"><h2>My Cart</h2></div>
                    <div class="col-sm-4">
                    </div>
                </div>
            </div>
            <table class="table table-bordered" style="background-color:lightgray;">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Parking Space</th>
                        <th>Date</th>
                        <th>Start from</th>
                        <th>Price (per hour)</th>
                        <th>Hours</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carts as $cart)
                    <tr>
                        <td><input type="checkbox" name="cid[]" form="payment-form" value="{{$cart->cid}}" onclick="cal()" checked><input type="hidden" name="subtotal[]" value="{{$cart->price*$cart->cartQty}}"></td>
                        <td width="10%"><img src="{{ asset('images/') }}/{{$cart->image}}" width="100%" alt="" class="img-fluid"></td>
                        <td>{{$cart->name}}</td>
                        <td>{{$cart->space}}</td>
                        <td>{{ $cart->bookDate ? \Carbon\Carbon::parse($cart->bookDate)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $cart->bookTime ? \Carbon\Carbon::parse($cart->bookTime)->format('H:i') : '-' }}</td>
                        <td>RM{{$cart->price}}</td>
                        <td>{{$cart->cartQty}}</td>
                        <td>RM {{$cart->price*$cart->cartQty}}</td>
                        <td>
                            <form method="POST" action="{{route('delete.Item',['id'=>$cart->cid])}}" onsubmit="return confirm('Remove this booking?')">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 delete" style="color:red">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <td colspan="8">&nbsp;</td>
                        <td>RM <strong id="sub">0.00</strong></td>
                        <td>&nbsp;</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
    
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <form action="{{ route('payment.post') }}" method="post" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="{{ config('services.stripe.key') }}" id="payment-form">
        @csrf
        <div class="card" style="background-color:lightgray; margin-bottom:50px; margin-left:105px; margin-right:-150px;">
            <div class="col-xl-12 col-xl-offset-12">
                <div class="col-xl-12 col-xl-offset-12">
                    <div class="panel panel-default credit-card-box">
                        <div class="panel-heading" >
                            <div>
                            <div class="card-header" style="background-color:lightgray; font-size:40px;">Card Payment</div>
                        
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="panel-body">
                    
                                <br>
                            
                                {{--
                                    These inputs deliberately have no name attribute: card
                                    details must never reach our server. Stripe.js reads them
                                    by class and posts only the token it returns.
                                --}}
                                <div class='row row'>
                                    <div class='col-xs-6 col-md-6 mb-3 required'>
                                        <label class='control-label'>Name on Card</label>
                                        <input class='form-control' size='4' type='text' required>
                                    </div>
                                    <div class='col-xs-12 col-md-6 mb-3 required'>
                                        <label class='control-label'>Card Number</label>
                                        <input autocomplete='off' class='form-control card-number' size='20' type='text' inputmode='numeric' required>
                                    </div>
                                </div>
                                <div class='row row'>
                                    <div class='col-xs-12 col-md-4 mb-3 cvc required'>
                                        <label class='control-label'>CVC</label>
                                        <input autocomplete='off' class='form-control card-cvc' placeholder='ex. 311' size='4' type='text' inputmode='numeric' required>
                                    </div>
                                    <div class='col-xs-12 col-md-4 mb-3 expiration required'>
                                        <label class='control-label'>Expiration Month</label>
                                        <input class='form-control card-expiry-month' placeholder='MM' size='2' type='text' inputmode='numeric' required>
                                    </div>
                                    <div class='col-xs-12 col-md-4 mb-3 expiration required'>
                                        <label class='control-label'>Expiration Year</label>
                                        <input class='form-control card-expiry-year' placeholder='YYYY' size='4' type='text' inputmode='numeric' required>
                                    </div>
                                </div>
                            </div>
                                {{-- Bootstrap 4 dropped .hide, and the JS below toggles it. --}}
                                <style>.hide{display:none}</style>
                                <div class='row row'>
                                    <div class='col-md-12 error mb-3 hide'>
                                    <div class='alert-danger alert'>Please correct the errors and try
                                        again.
                                    </div>
                                    </div>
                                </div>
                                <div class="row row">
                                    <div class="col-xs-12">
                                        <button class="btn btn-primary btn-lg w-100" type="submit" style="border-radius:20px; background-color:green; border:green;">Pay Now</button>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
// The Vite bundle loads as type="module", which is deferred, so jQuery is not
// defined while this script is parsed. DOMContentLoaded fires after deferred
// scripts run, so waiting for it guarantees window.$ exists.
document.addEventListener('DOMContentLoaded', function () {
  var $form = $(".require-validation");

  function showError(message) {
    $form.find('div.error').removeClass('hide').find('.alert').text(message);
  }

  $('form.require-validation').bind('submit', function(e) {
    var inputSelector = ['input[type=email]', 'input[type=password]', 'input[type=text]', 'input[type=file]', 'textarea'].join(', '),
    $inputs = $form.find('.required').find(inputSelector),
    valid = true;
    $form.find('div.error').addClass('hide');
    $('.has-error').removeClass('has-error');
    $inputs.each(function(i, el) {
        var $input = $(el);
        if ($input.val() === '') {
            $input.parent().addClass('has-error');
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
          $('.error')
              .removeClass('hide')
              .find('.alert')
              .text(response.error.message);
      } else {
          /* token contains id, last4, and card type */
          var token = response['id'];
          $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
          $form.get(0).submit();
      }
  }
});

</script>
&nbsp;
@endsection