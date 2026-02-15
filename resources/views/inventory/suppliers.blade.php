@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Suppliers</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage inventory suppliers and vendor contacts.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline text-sm">← Items</a>
        <button onclick="document.getElementById('createSupModal').classList.remove('hidden')" class="btn btn-primary text-sm">+ Add Supplier</button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($suppliers as $sup)
    <div class="card card-body">
        <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-slate-900">{{ $sup->name }}</h3>
            <span class="text-xs text-slate-400">{{ $sup->items_count ?? 0 }} items</span>
        </div>
        @if($sup->contact_person)<p class="text-sm text-slate-600">{{ $sup->contact_person }}</p>@endif
        @if($sup->email)<p class="text-sm text-slate-500">{{ $sup->email }}</p>@endif
        @if($sup->phone)<p class="text-sm text-slate-500">{{ $sup->phone }}</p>@endif
        @if($sup->address)<p class="text-xs text-slate-400 mt-1">{{ $sup->address }}</p>@endif
        <div class="action-links mt-3">
            <button onclick="editSupplier({{ json_encode($sup) }})">Edit</button>
            @if(($sup->items_count ?? 0) === 0)
            <form method="POST" action="{{ route('inventory.suppliers.destroy', $sup->id) }}" class="inline" data-confirm="Delete this supplier?">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600">Delete</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full text-sm text-slate-400 text-center py-8">No suppliers added yet.</div>
    @endforelse
</div>

{{-- Create Modal --}}
<div id="createSupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Supplier</h3>
        <form method="POST" action="{{ route('inventory.suppliers.store') }}">
            @csrf
            @include('inventory._supplier-form')
            <div class="flex items-center gap-2 pt-3">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" onclick="document.getElementById('createSupModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editSupModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Edit Supplier</h3>
        <form method="POST" id="editSupForm">
            @csrf @method('PUT')
            @include('inventory._supplier-form', ['prefix' => 'edit'])
            <div class="flex items-center gap-2 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" onclick="document.getElementById('editSupModal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editSupplier(sup) {
    document.getElementById('editSupForm').action = '/inventory/suppliers/' + sup.id;
    document.getElementById('edit_name').value = sup.name || '';
    document.getElementById('edit_contact_person').value = sup.contact_person || '';
    document.getElementById('edit_email').value = sup.email || '';
    document.getElementById('edit_phone').value = sup.phone || '';
    document.getElementById('edit_address').value = sup.address || '';
    document.getElementById('editSupModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
