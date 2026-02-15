@extends('layouts.app')
@section('title', 'Position Permissions — ' . $position->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('positions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Positions</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">{{ $position->name }} — Access Permissions</h1>
    <p class="text-sm text-slate-500 mt-0.5">Configure what users with this position can access</p>
</div>

<form method="POST" action="{{ route('positions.permissions.update', $position) }}">
    @csrf @method('PUT')

    @foreach($catalog as $domain => $domainInfo)
    <div class="card mb-4">
        <div class="card-header">
            <span class="font-semibold">{{ $domainInfo['label'] }}</span>
            <span class="text-xs text-slate-400 ml-2">{{ $domainInfo['description'] }}</span>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="table-basic">
                <thead>
                    <tr>
                        <th class="w-1/3">Resource</th>
                        <th>Description</th>
                        <th class="w-48">Access Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($domainInfo['resources'] as $resource => $resInfo)
                    @php $key = "{$domain}.{$resource}"; @endphp
                    <tr>
                        <td class="font-medium text-slate-900">
                            {{ $resInfo['label'] }}
                            @if(!empty($resInfo['self_service']))
                                <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-blue-100 text-blue-600 ml-1">Self-service</span>
                            @endif
                        </td>
                        <td class="text-sm text-slate-500">{{ $resInfo['description'] }}</td>
                        <td>
                            <select name="permissions[{{ $key }}]" class="input-text text-sm">
                                <option value="none" {{ ($currentPerms[$key] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
                                <option value="read" {{ ($currentPerms[$key] ?? '') === 'read' ? 'selected' : '' }}>Read</option>
                                <option value="write" {{ ($currentPerms[$key] ?? '') === 'write' ? 'selected' : '' }}>Write</option>
                                <option value="manage" {{ ($currentPerms[$key] ?? '') === 'manage' ? 'selected' : '' }}>Manage</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="flex items-center gap-3 mt-2 mb-8">
        <button type="submit" class="btn btn-primary">Save Permissions</button>
        <a href="{{ route('positions.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>
@endsection
