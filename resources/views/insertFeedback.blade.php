@extends('layout')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card booking-card">

        <div class="auth-brand">
            <h1>{{ __('We value your feedback') }}</h1>
            <p>{{ __('Tell us how we can make parking with Quick Park better.') }}</p>
        </div>

        @include('partials.alerts')

        <form method="POST" action="{{ route('addFeedback') }}" novalidate>
            @csrf

            <div class="form-floating mb-1">
                <textarea id="feedbackContent"
                          name="feedbackContent"
                          class="form-control feedback-text @error('feedbackContent') is-invalid @enderror"
                          placeholder="{{ __('Your feedback') }}"
                          maxlength="255"
                          required>{{ old('feedbackContent') }}</textarea>
                <label for="feedbackContent">{{ __('How can we improve?') }}</label>
            </div>
            <div class="form-text text-end mb-3"><span id="feedback-count">0</span> / 255</div>

            <button type="submit" class="btn btn-qpark w-100">{{ __('Send Feedback') }}</button>
        </form>

    </div>
</div>

<script>
    (function () {
        var box = document.getElementById('feedbackContent');
        var count = document.getElementById('feedback-count');
        function update() { count.textContent = box.value.length; }
        box.addEventListener('input', update);
        update();
    })();
</script>
@endsection
