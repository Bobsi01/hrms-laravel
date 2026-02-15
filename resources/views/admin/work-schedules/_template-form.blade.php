<div class="space-y-3">
    <div>
        <label class="block text-sm font-medium text-slate-700 required">Template Name</label>
        <input type="text" name="name" class="input-text mt-1" value="{{ $tpl->name ?? old('name') }}" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" class="input-text mt-1" rows="2">{{ $tpl->description ?? old('description') }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 required">Time In</label>
            <input type="time" name="time_in" class="input-text mt-1" value="{{ $tpl->time_in ?? old('time_in', '08:00') }}" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 required">Time Out</label>
            <input type="time" name="time_out" class="input-text mt-1" value="{{ $tpl->time_out ?? old('time_out', '17:00') }}" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700">Break (minutes)</label>
            <input type="number" name="break_minutes" class="input-text mt-1" value="{{ $tpl->break_minutes ?? old('break_minutes', 60) }}" min="0">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Work Days</label>
            <input type="text" name="work_days" class="input-text mt-1" value="{{ $tpl->work_days ?? old('work_days', 'Mon-Fri') }}" placeholder="Mon-Fri">
        </div>
    </div>
    <div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" {{ ($tpl->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300">
            <span class="text-slate-700">Active</span>
        </label>
    </div>
</div>
