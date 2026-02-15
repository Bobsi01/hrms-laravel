@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Inventory Categories</h1>
        <p class="text-sm text-slate-500 mt-0.5">Organize items by category.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline text-sm">← Items</a>
        <button onclick="document.getElementById('createCatModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Category</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($categories->isEmpty())
            <p class="text-sm text-slate-400 py-4 text-center">No categories yet.</p>
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
                    @foreach($categories as $cat)
                    <tr>
                        <td class="font-medium">{{ $cat->name }}</td>
                        <td class="text-sm text-slate-500">{{ Str::limit($cat->description ?? '', 60) }}</td>
                        <td>{{ $cat->items_count ?? 0 }}</td>
                        <td>
                            <div class="action-links">
                                <button onclick="editCategory({{ json_encode($cat) }})">Edit</button>
                                @if(($cat->items_count ?? 0) === 0)
                                <form method="POST" action="{{ route('inventory.categories.destroy', $cat->id) }}" class="inline" data-confirm="Delete this category?">
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
<div id="createCatModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Category</h3>
        <form method="POST" action="{{ route('inventory.categories.store') }}">
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
                    <button type="button" onclick="document.getElementById('createCatModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editCatModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Edit Category</h3>
        <form method="POST" id="editCatForm">
            @csrf @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 required">Name</label>
                    <input type="text" name="name" id="editCatName" class="input-text mt-1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" id="editCatDesc" class="input-text mt-1" rows="2"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" onclick="document.getElementById('editCatModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editCategory(cat) {
    document.getElementById('editCatForm').action = '/inventory/categories/' + cat.id;
    document.getElementById('editCatName').value = cat.name;
    document.getElementById('editCatDesc').value = cat.description || '';
    document.getElementById('editCatModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
