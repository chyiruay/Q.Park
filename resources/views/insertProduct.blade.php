@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Add Car Park</h1>
        <p>It appears on the customer listing as soon as it is saved.</p>
    </div>

    <div class="admin-form">
        @include('partials.alerts')

        <form method="POST" action="{{ route('addProduct') }}" enctype="multipart/form-data" class="admin-panel" novalidate>
            @csrf

            <div class="mb-3">
                <label for="productName" class="form-label fw-semibold">Name</label>
                <input type="text" id="productName" name="productName"
                       class="form-control @error('productName') is-invalid @enderror"
                       value="{{ old('productName') }}" maxlength="255" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="price" class="form-label fw-semibold">Price per Hour</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number" id="price" name="price"
                               class="form-control @error('price') is-invalid @enderror"
                               min="0" max="9999" step="0.01" value="{{ old('price') }}" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="categoryID" class="form-label fw-semibold">Category</label>
                    <select id="categoryID" name="categoryID" class="form-select @error('categoryID') is-invalid @enderror" required>
                        @foreach ($categoryID as $category)
                            <option value="{{ $category->id }}" @selected(old('categoryID') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach ([
                    ['field' => 'product-image', 'label' => 'Car Park Photo'],
                    ['field' => 'product-floorPlan', 'label' => 'Floor Plan'],
                ] as $upload)
                    <div class="col-sm-6">
                        <label for="{{ $upload['field'] }}" class="form-label fw-semibold">{{ $upload['label'] }}</label>
                        {{-- nothing to show until a file is picked, unlike the edit form --}}
                        <img src="" alt="" class="admin-upload-preview d-none" id="{{ $upload['field'] }}-preview">
                        <input type="file" id="{{ $upload['field'] }}" name="{{ $upload['field'] }}"
                               class="form-control form-control-sm @error($upload['field']) is-invalid @enderror"
                               accept="image/jpeg,image/png,image/webp" data-preview="{{ $upload['field'] }}-preview" required>
                        <div class="form-text">Required. JPG, PNG or WebP, max 4 MB.</div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex flex-column flex-sm-row-reverse gap-2">
                <button type="submit" class="btn btn-qpark flex-sm-fill">Add Car Park</button>
                <a href="{{ route('viewProduct') }}" class="btn btn-outline-secondary flex-sm-fill">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    // show the chosen file before saving; the preview starts hidden on this form
    document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files[0]) {
                var preview = document.getElementById(input.dataset.preview);
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('d-none');
            }
        });
    });
</script>
@endsection
