@extends('layoutadmin')

@section('content')
@foreach ($products as $product)
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Edit Car Park</h1>
        <p>{{ $product->name }}</p>
    </div>

    <div class="admin-form">
        @include('partials.alerts')

        <form method="POST" action="{{ route('updateProduct') }}" enctype="multipart/form-data" class="admin-panel" novalidate>
            @csrf
            <input type="hidden" name="hid" value="{{ $product->id }}">

            <div class="mb-3">
                <label for="productName" class="form-label fw-semibold">Name</label>
                <input type="text" id="productName" name="productName"
                       class="form-control @error('productName') is-invalid @enderror"
                       value="{{ old('productName', $product->name) }}" maxlength="255" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="price" class="form-label fw-semibold">Price per Hour</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" id="price" name="price"
                               class="form-control @error('price') is-invalid @enderror"
                               min="0" max="9999" step="0.01" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="categoryID" class="form-label fw-semibold">Category</label>
                    <select id="categoryID" name="categoryID" class="form-select @error('categoryID') is-invalid @enderror" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('categoryID', $product->categoryID) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach ([
                    ['field' => 'product-image', 'label' => 'Car Park Photo', 'src' => asset('images/' . $product->image)],
                    ['field' => 'product-floorPlan', 'label' => 'Floor Plan', 'src' => asset('floorPlan/' . $product->floorPlan)],
                ] as $upload)
                    <div class="col-sm-6">
                        <label for="{{ $upload['field'] }}" class="form-label fw-semibold">{{ $upload['label'] }}</label>
                        <img src="{{ $upload['src'] }}" alt="Current {{ strtolower($upload['label']) }}" class="admin-upload-preview" id="{{ $upload['field'] }}-preview">
                        <input type="file" id="{{ $upload['field'] }}" name="{{ $upload['field'] }}"
                               class="form-control form-control-sm @error($upload['field']) is-invalid @enderror"
                               accept="image/jpeg,image/png,image/webp" data-preview="{{ $upload['field'] }}-preview">
                        <div class="form-text">Leave empty to keep the current image. JPG, PNG or WebP, max 4 MB.</div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-column flex-sm-row-reverse gap-2">
                <button type="submit" class="btn btn-qpark flex-sm-fill">Save Changes</button>
                <a href="{{ route('viewProduct') }}" class="btn btn-outline-secondary flex-sm-fill">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endforeach

<script>
    // show the chosen file in place of the current image before saving
    document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files[0]) {
                document.getElementById(input.dataset.preview).src = URL.createObjectURL(input.files[0]);
            }
        });
    });
</script>
@endsection
