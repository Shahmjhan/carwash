@extends('layouts.app')
@section('content')
<div class="page-head">
    <h1>{{ $selectedParent ? 'New Subcategory' : 'New Main Category' }}</h1>
</div>

<div class="panel form-panel">
    <form method="post" action="{{ route('categories.store') }}">
        @csrf
        <div class="form-grid">
            <label>Name*
                <input name="name" required>
            </label>
            @if($selectedParent)
                <label>Parent Category
                    <select name="parent_id">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedParent == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <input type="hidden" name="parent_id" value="">
            @endif
        </div>
        <button class="primary">Create {{ $selectedParent ? 'Subcategory' : 'Main Category' }}</button>
    </form>
</div>
@endsection
