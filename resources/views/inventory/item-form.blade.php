@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('inventory.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Inventory</a>
    <h1 class="text-xl font-bold text-slate-900 mt-2">{{ isset($item) ? 'Edit Item: ' . $item->name : 'Add New Item' }}</h1>
</div>

<div class="card max-w-2xl">
    <div class="card-body">
        <form method="POST" action="{{ isset($item) ? route('inventory.update', $item->id) : route('inventory.store') }}">
            @csrf
            @if(isset($item)) @method('PUT') @endif

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Item Name</label>
                        <input type="text" name="name" class="input-text mt-1 @error('name') input-error @enderror"
                            value="{{ old('name', $item->name ?? '') }}" required>
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">SKU</label>
                        <input type="text" name="sku" class="input-text mt-1" value="{{ old('sku', $item->sku ?? '') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" class="input-text mt-1" rows="2">{{ old('description', $item->description ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Category</label>
                        <select name="category_id" class="input-text mt-1">
                            <option value="">None</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Supplier</label>
                        <select name="supplier_id" class="input-text mt-1">
                            <option value="">None</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id', $item->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Location</label>
                        <select name="location_id" class="input-text mt-1">
                            <option value="">None</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id', $item->location_id ?? '') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 required">Unit Price</label>
                        <input type="number" name="unit_price" class="input-text mt-1" step="0.01" min="0"
                            value="{{ old('unit_price', $item->unit_price ?? '0.00') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Cost Price</label>
                        <input type="number" name="cost_price" class="input-text mt-1" step="0.01" min="0"
                            value="{{ old('cost_price', $item->cost_price ?? '0.00') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Reorder Level</label>
                        <input type="number" name="reorder_level" class="input-text mt-1" min="0"
                            value="{{ old('reorder_level', $item->reorder_level ?? '10') }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Unit of Measure</label>
                        <input type="text" name="unit" class="input-text mt-1" value="{{ old('unit', $item->unit ?? 'pcs') }}" placeholder="pcs, kg, ml...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Barcode</label>
                        <input type="text" name="barcode" class="input-text mt-1" value="{{ old('barcode', $item->barcode ?? '') }}">
                    </div>
                </div>

                @if(!isset($item))
                <div>
                    <label class="block text-sm font-medium text-slate-700">Initial Stock Quantity</label>
                    <input type="number" name="initial_quantity" class="input-text mt-1" value="{{ old('initial_quantity', 0) }}" min="0">
                    <p class="text-xs text-slate-400 mt-0.5">Sets the opening stock balance. Leave 0 if no initial stock.</p>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700">Expiry Date</label>
                    <input type="date" name="expiry_date" class="input-text mt-1" value="{{ old('expiry_date', isset($item) && $item->expiry_date ? $item->expiry_date : '') }}">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">{{ isset($item) ? 'Update Item' : 'Create Item' }}</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
