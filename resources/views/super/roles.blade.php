@extends('layouts.app')
@section('title', 'Role Management — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ changeId: null, changeName: '', newRole: '', newCounty: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-white">Role Management</h1>
                <p class="text-sm text-gray-400 mt-1">All platform users and their assigned roles</p>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
                Tier 1
            </span>
        </div>
        <a href="/admin/invitations"
           class="px-4 py-2.5 bg-yellow-400 text-white font-medium rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            + Invite User
        </a>
    </div>

    {{-- Role count badges --}}
    <div class="flex flex-wrap gap-2">
        @foreach($roleCounts as $role => $count)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
            {{ str_replace('_', ' ', $role) }}
            <span class="bg-gray-600 text-gray-200 rounded-full px-1.5 py-0.5 text-xs">{{ $count }}</span>
        </span>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="role"
                class="px-3 py-2.5 border border-gray-600 bg-gray-800 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
            <option value="">All roles</option>
            @foreach($allRoles as $role)
                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $role) }}
                </option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name or email..."
               class="px-3 py-2.5 border border-gray-600 bg-gray-800 text-white placeholder-gray-500 rounded-lg text-sm w-56 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
        <button type="submit"
                class="px-4 py-2.5 bg-yellow-400 text-white font-medium rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['role', 'search']))
            <a href="/super/roles"
               class="px-4 py-2.5 border border-gray-600 text-gray-400 rounded-lg text-sm hover:bg-gray-700/50">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Email</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($users as $u)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 font-medium text-gray-200">{{ $u->name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $u->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-300">
                                {{ str_replace('_', ' ', $u->role_name ?? 'No role') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <button @click="changeId = {{ $u->id }};
                                            changeName = '{{ addslashes($u->name) }}';
                                            newRole = '{{ $u->role_name ?? '' }}';
                                            newCounty = ''"
                                    class="px-3 py-1.5 border border-gray-600 text-gray-400 rounded-lg text-xs hover:bg-gray-700/50">
                                Change Role
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-gray-400 text-sm">
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
        New users are added via <a href="/admin/invitations" class="text-yellow-400 hover:underline">Invitations</a>.
    </p>

    {{-- Change Role modal --}}
    <div x-show="changeId !== null"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl border border-gray-700">
            <h3 class="text-base font-semibold text-white">Change Role</h3>
            <p class="text-sm text-gray-400 mt-1">
                Changing role for <strong x-text="changeName"></strong>
            </p>
            <form method="POST"
                  :action="'/api/v1/admin/users/' + changeId + '/role'"
                  class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">New Role</label>
                    <select name="role" x-model="newRole"
                            class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        @foreach($allRoles as $role)
                            <option value="{{ $role }}">{{ str_replace('_', ' ', $role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="newRole === 'network_field_agent' || newRole === 'sales_rep'">
                    <label class="block text-xs font-medium text-gray-400 mb-1">County Assignment</label>
                    <input type="text" name="county" x-model="newCounty"
                           placeholder="e.g. Kilifi"
                           class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white placeholder-gray-500 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm font-bold hover:bg-yellow-500">
                        Save Role
                    </button>
                    <button type="button" @click="changeId = null"
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
