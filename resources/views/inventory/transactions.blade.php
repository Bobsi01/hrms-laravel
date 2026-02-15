@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Transactions</h1>
        <p class="text-sm text-slate-500 mt-0.5">Sales transaction history.</p>
    </div>
    <div>
        <a href="{{ route('inventory.pos') }}" class="btn btn-primary text-sm">Open POS</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search transaction ID..." class="input-text text-sm flex-1">
            <select name="status" class="input-text text-sm w-36">
                <option value="">All Status</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-text text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-text text-sm">
            <button type="submit" class="btn btn-outline text-sm">Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table-basic">
            <thead>
                <tr>
                    <th>Transaction #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Cashier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td class="font-mono text-sm">{{ $txn->transaction_number ?? '#' . $txn->id }}</td>
                    <td class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, h:i A') }}</td>
                    <td class="text-sm">{{ $txn->item_count ?? '—' }}</td>
                    <td class="font-medium">₱{{ number_format($txn->total_amount ?? 0, 2) }}</td>
                    <td class="text-sm">{{ ucfirst($txn->payment_method ?? '—') }}</td>
                    <td>
                        @if($txn->status === 'completed')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Completed</span>
                        @elseif($txn->status === 'voided')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Voided</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-500">{{ ucfirst($txn->status ?? '') }}</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-500">{{ $txn->cashier_name ?? '—' }}</td>
                    <td>
                        <div class="action-links">
                            @if($txn->status === 'completed')
                            <form method="POST" action="{{ route('inventory.transactions.void', $txn->id) }}" class="inline" data-confirm="Void this transaction? Stock will be restored.">
                                @csrf
                                <button type="submit" class="text-red-600">Void</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-slate-400 py-4">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
