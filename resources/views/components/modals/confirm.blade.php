{{-- Confirm Modal --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-confirm-close></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl ring-1 ring-black/5 w-full max-w-sm">
            <div class="px-4 py-3 border-b font-semibold flex items-center justify-between">
                <span id="confirmModalTitle">Confirm Action</span>
                <button class="text-gray-500 hover:text-gray-700" title="Close" data-confirm-close aria-label="Close">&times;</button>
            </div>
            <div class="p-4 space-y-4">
                <div id="confirmMessage" class="text-sm text-gray-700">Are you sure?</div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="px-3 py-2 rounded border" data-confirm-close>Cancel</button>
                    <button type="button" class="px-3 py-2 rounded bg-red-600 text-white" id="confirmYes">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>
