@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Categories</h1>
    </div>
    <a class="primary" href="{{ route('categories.create') }}">+ New Main Category</a>
</div>

<div class="panel">
    @forelse($categories as $category)
        <div class="listrow">
            <div style="flex: 1;">
                <b>{{ $category->name }}</b>
                <span>{{ $category->children->count() }} subcategories</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('categories.create') }}?parent_id={{ $category->id }}" class="secondary">+ Add Subcategory</a>
                <a href="{{ route('categories.edit', $category) }}" class="secondary">Edit</a>
            </div>
        </div>
        @if($category->children->count() > 0)
            @foreach($category->children as $child)
                <div class="listrow" style="padding-left: 40px;">
                    <div style="flex: 1;">
                        <b>↳ {{ $child->name }}</b>
                        <span>Subcategory</span>
                    </div>
                    <a href="{{ route('categories.edit', $child) }}" class="secondary">Edit</a>
                </div>
            @endforeach
        @endif
    @empty
        <p class="empty">No categories found. Create main categories first.</p>
    @endforelse
</div>

{{ $categories->links() }}
@endsection
