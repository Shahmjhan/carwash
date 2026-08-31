@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Users</h1>
        <p>Manage system users and their permissions</p>
    </div>
    <a href="{{ route('users.create') }}" class="primary">+ Add User</a>
</div>

<div class="panel">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Branch</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ optional($user->branch)->name ?? 'All' }}</td>
                    <td>
                        @if($user->active)
                            <span class="badge" style="background: #27ae60;">Active</span>
                        @else
                            <span class="badge" style="background: #e74c3c;">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('users.edit', $user) }}">Edit</a>
                        @if($user->id !== auth()->id())
                            <form method="post" action="{{ route('users.destroy', $user) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection