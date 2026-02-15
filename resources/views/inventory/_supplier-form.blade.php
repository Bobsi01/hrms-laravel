@php $pfx = $prefix ?? ''; @endphp
<div class="space-y-3">
    <div>
        <label class="block text-sm font-medium text-slate-700 required">Supplier Name</label>
        <input type="text" name="name" id="{{ $pfx }}_name" class="input-text mt-1" required value="{{ $sup->name ?? '' }}">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Contact Person</label>
        <input type="text" name="contact_person" id="{{ $pfx }}_contact_person" class="input-text mt-1" value="{{ $sup->contact_person ?? '' }}">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700">Email</label>
            <input type="email" name="email" id="{{ $pfx }}_email" class="input-text mt-1" value="{{ $sup->email ?? '' }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Phone</label>
            <input type="text" name="phone" id="{{ $pfx }}_phone" class="input-text mt-1" value="{{ $sup->phone ?? '' }}">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Address</label>
        <textarea name="address" id="{{ $pfx }}_address" class="input-text mt-1" rows="2">{{ $sup->address ?? '' }}</textarea>
    </div>
</div>
