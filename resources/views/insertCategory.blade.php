@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Add Category</h1>
        <p>Car parks are grouped under categories on the customer listing.</p>
    </div>

    <div class="admin-form admin-form--narrow">
        @include('partials.alerts')

        <form method="POST" action="{{ route('addCategory') }}" class="admin-panel" novalidate>
            @csrf

            <div class="mb-4">
                <label for="categoryName" class="form-label fw-semibold">Name</label>
                <input type="text" id="categoryName" name="categoryName"
                       class="form-control @error('categoryName') is-invalid @enderror"
                       value="{{ old('categoryName') }}" maxlength="255" required autofocus>
                <div class="form-text">Must not match an existing category.</div>
            </div>

            <div class="d-flex flex-column flex-sm-row-reverse gap-2">
                <button type="submit" class="btn btn-qpark flex-sm-fill">Add Category</button>
                <a href="{{ route('viewCategory') }}" class="btn btn-outline-secondary flex-sm-fill">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
