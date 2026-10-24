@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Categories</h1>
        <p>{{ $categories->count() }} {{ Str::plural('category', $categories->count()) }} listed.</p>
    </div>

    @include('partials.alerts')
    <div class="text-end mb-3">
        <a href="{{ route('insertCategory') }}" class="btn btn-qpark">+ Add New Category</a>
    </div>
    <div class="admin-panel">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Car Parks</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="text-muted">{{ $category->id }}</td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td><span class="badge admin-badge--customer">{{ $category->productCount }}</span></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('editCategory', ['id' => $category->id]) }}" class="btn btn-sm btn-outline-secondary admin-icon-btn" title="Edit" aria-label="Edit {{ $category->name }}">
                                        <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('deleteCategory', ['id' => $category->id]) }}" class="d-inline" onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Delete" aria-label="Delete {{ $category->name }}">
                                            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="admin-panel__empty">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>
@endsection
