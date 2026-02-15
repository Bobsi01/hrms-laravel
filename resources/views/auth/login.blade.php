@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md mx-auto px-4">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-2xl mb-4 shadow-lg shadow-indigo-200/50">H</div>
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to {{ config('hrms.company.name', 'HRMS') }}</p>
    </div>

    <div class="card">
        <div class="card-body p-6 sm:p-8">
            @if($errors->any())
            <div class="mb-4 p-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5 required">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all"
                        placeholder="you@company.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5 required">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all"
                        placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-600">Remember me</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-full py-2.5">Sign in</button>
            </form>
        </div>
    </div>
    <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} {{ config('hrms.company.name') }}. All rights reserved.</p>
</div>
@endsection
