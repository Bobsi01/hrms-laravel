@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Storage Locations</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage storage areas, warehouses, and shelves.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline text-sm">← Items</a>
        <button onclick="document.getElementById('createLocModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Location</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($locations->isEmpty())
            <p class="text-sm text-slate-400 py-4 text-center">No locations defined.</p>
        @else
            <table class="table-basic">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $loc)
                    <tr>
                        <td class="font-medium">{{ $loc->name }}</td>
                        <td class="text-sm text-slate-500">{{ Str::limit($loc->description ?? '', 60) }}</td>
                        <td>{{ $loc->items_count ?? 0 }}</td>
                        <td>
                            <div class="action-links">
                                <button onclick="editLocation({{ json_encode($loc) }})">Edit</button>
                                @if(($loc->items_count ?? 0) === 0)
                                <form method="POST" action="{{ route('inventory.locations.destroy', $loc->id) }}" class="inline" data-confirm="Delete this location?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div id="createLocModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Location</h3>
        <form method="POST" action="{{ route('inventory.locations.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Name</label>
                    <input type="text" name="name" class="input-text mt-1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" class="input-text mt-1" rows="2"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <button type="button" onclick="document.getElementById('createLocModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editLocModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Edit Location</h3>
        <form method="POST" id="editLocForm">
            @csrf @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Name</label>
                    <input type="text" name="name" id="editLocName" class="input-text mt-1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" id="editLocDesc" class="input-text mt-1" rows="2"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="document.getElementById('editLocModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editLocation(loc) {
    document.getElementById('editLocForm').action = '/inventory/locations/' + loc.id;
    document.getElementById('editLocName').value = loc.name;
    document.getElementById('editLocDesc').value = loc.description || '';
    document.getElementById('editLocModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
