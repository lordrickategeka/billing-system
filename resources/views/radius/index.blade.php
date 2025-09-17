@extends('layouts.app')

@section('content')
<h2>RADIUS Users</h2>
<a href="{{ route('radius.create') }}">Add User</a>

<table>
    <tr>
        <th>Customer</th>
        <th>Product</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->customer->name }}</td>
            <td>{{ $user->product->name }}</td>
            <td>{{ $user->status }}</td>
            <td>
                <a href="{{ route('radius.edit', $user->id) }}">Edit</a>
                <form action="{{ route('radius.destroy', $user->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>
@endsection
