@extends('layoutadmin')

@section('content')
@foreach ($categories as $category)
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Edit Category</h1>
        <p>{{ $category->name }}</p>
    </div>

    <div class="admin-form admin-form--narrow">
        @include('partials.alerts')

        <form method="POST" action="{{ route('updateCategory') }}" class="admin-panel" novalidate>
            @csrf
            <input type="hidden" name="hid" value="{{ $category->id }}">

            <div class="mb-4">
                <label for="categoryName" class="form-label fw-semibold">Name</label>
                <input type="text" id="categoryName" name="categoryName"
                       class="form-control @error('categoryName') is-invalid @enderror"
                       value="{{ old('categoryName', $category->name) }}" maxlength="255" required autofocus>
            </div>

            <div class="d-flex flex-column flex-sm-row-reverse gap-2">
                <button type="submit" class="btn btn-qpark flex-sm-fill">Save Changes</button>
                <a href="{{ route('viewCategory') }}" class="btn btn-outline-secondary flex-sm-fill">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
