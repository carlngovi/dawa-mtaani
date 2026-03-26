@extends('layouts.app')
@section('title', 'Role Management — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ changeId: null, changeName: '', newRole: '', newCounty: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                <p class="text-sm text-gray-500 mt-1">All platform users and their assigned roles</p>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                Tier 1
            </span>
        </div>
        <a href="/admin/invitations"
           class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            + Invite User
        </a>
    </div>

    {{-- Role count badges --}}
    <div class="flex flex-wrap gap-2">
        @foreach($roleCounts as $role => $count)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
            {{ str_replace('_', ' ', $role) }}
            <span class="bg-gray-300 text-gray-700 rounded-full px-1.5 py-0.5 text-xs">{{ $count }}</span>
        </span>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="role"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All roles</option>
            @foreach($allRoles as $role)
                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $role) }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name or email..."
               class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit"
                class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['role', 'search']))
            <a href="/super/roles"
               class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Email</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">County</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $u->name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell">{{ $u->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ str_replace('_', ' ', $u->role_name ?? 'No role') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ $u->county ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <button @click="changeId = {{ $u->id }};
                                            changeName = '{{ addslashes($u->name) }}';
                                            newRole = '{{ $u->role_name ?? '' }}';
                                            newCounty = '{{ $u->county ?? '' }}'"
                                    class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg text-xs hover:bg-gray-50">
                                Change Role
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No users found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $users->links() }}</div>

    <p class="text-xs text-gray-400">
        Role changes take effect on the user's next login.
        New users are added via <a href="/admin/invitations" class="text-green-700 hover:underline">Invitations</a>.
    </p>

    {{-- Change Role modal --}}
    <div x-show="changeId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-gray-900">Change Role</h3>
            <p class="text-sm text-gray-500 mt-1">
                Changing role for <strong x-text="changeName"></strong>
            </p>
            <form method="POST"
                  :action="'/api/v1/admin/users/' + changeId + '/role'"
                  class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Role</label>
                    <select name="role" x-model="newRole"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        @foreach($allRoles as $role)
                            <option value="{{ $role }}">{{ str_replace('_', ' ', $role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="newRole === 'network_field_agent' || newRole === 'sales_rep'">
                    <label class="block text-xs font-medium text-gray-700 mb-1">County Assignment</label>
                    <input type="text" name="county" x-model="newCounty"
                           placeholder="e.g. Kilifi"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                        Save Role
                    </button>
                    <button type="button" @click="changeId = null"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
