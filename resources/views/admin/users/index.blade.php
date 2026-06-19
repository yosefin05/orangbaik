@extends('layouts.admin')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    Data User
</h1>

<table class="w-full bg-white shadow rounded">

    <thead>

        <tr class="bg-gray-100">

            <th class="p-3 text-left">
                Nama
            </th>

            <th class="p-3 text-left">
                Email
            </th>

            <th class="p-3 text-left">
                Role
            </th>

            <th class="p-3 text-left">
                Aksi
            </th>

        </tr>

    </thead>

    <tbody>

        @foreach($users as $user)

        <tr class="border-t">

            <td class="p-3">
                {{ $user->name }}
            </td>

            <td class="p-3">
                {{ $user->email }}
            </td>

            <td class="p-3">
                {{ $user->role }}
            </td>

            <td class="p-3">

                <a
                    href="{{ route('admin.users.show', $user) }}"
                    class="text-blue-500"
                >
                    Detail
                </a>

            </td>

        </tr>

        @endforeach

    </tbody>

</table>

<div class="mt-4">
    {{ $users->links() }}
</div>

@endsection