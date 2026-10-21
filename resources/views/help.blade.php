@extends('layouthelp')

@section('content')
<section class="help-hero">
    <div class="container">
        <h1 class="help-hero__title">Help &amp; Support</h1>
        <p class="help-hero__subtitle">
            Answers to the questions we get asked most.
        </p>
    </div>
</section>

<section class="container py-5">
    <h2 class="help-section-title">Need Help?</h2>

    <div class="row g-4">

        <div class="col-12 col-lg-4">
            <article class="help-card">
                <h3 class="help-card__title">How do I book a parking space?</h3>
                <p class="help-card__body">
                    Pick a car park from <a href="{{ route('Product') }}">Car Parks</a> and it is
                    added to <strong>My Cart</strong>. Open the cart, tick the spaces you want,
                    enter your card details and pay.
                </p>
            </article>
        </div>

        <div class="col-12 col-lg-4">
            <article class="help-card">
                <h3 class="help-card__title">How do I edit my profile?</h3>
                <p class="help-card__body">
                    Open the menu under your name in the top right, choose
                    <strong>My Profile</strong>, then click <strong>Edit</strong>, update your
                    details and click <strong>Save</strong>.
                </p>
            </article>
        </div>

        <div class="col-12 col-lg-4">
            <article class="help-card">
                <h3 class="help-card__title">How do I contact Quick Park?</h3>
                <p class="help-card__body">
                    Use <a href="{{ route('insertFeedback') }}">Feedback</a> in the menu under
                    your name to tell us what needs improving, or find our phone number and
                    address in the footer below.
                </p>
            </article>
        </div>

    </div>

    <p class="help-footnote">
        Still stuck? <a href="{{ route('insertFeedback') }}">Send us a message</a> and we'll get back to you.
    </p>
</section>
@endsection
