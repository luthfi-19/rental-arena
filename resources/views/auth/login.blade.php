@extends('layouts.app')
@section('title', 'Login Staff')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 relative bg-arena-bg">
    <div class="absolute w-96 h-96 bg-electric-blue opacity-10 blur-[120px] top-10 left-10 rounded-full"></div>
    <div class="absolute w-96 h-96 bg-critical-red opacity-5 blur-[120px] bottom-10 right-10 rounded-full"></div>

    <div class="w-full max-w-md bg-arena-card border border-gray-800 p-8 rounded-lg shadow-2xl relative z-10">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold tracking-wider text-white">ARENA SYSTEM LOG IN</h2>
            <p class="text-xs text-gray-500 uppercase mt-1 tracking-widest">Sistem Manajemen Operasional Arena & Rental</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-critical-red/20 border border-critical-red text-red-200 text-xs rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Username Staff</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full bg-arena-bg border border-gray-700 rounded px-4 py-3 text-sm text-gray-200 focus:outline-none focus:border-electric-blue transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-arena-bg border border-gray-700 rounded px-4 py-3 text-sm text-gray-200 focus:outline-none focus:border-electric-blue transition-colors">
            </div>

            <button type="submit" 
                class="w-full bg-electric-blue text-white font-bold py-3 px-4 rounded text-sm hover:bg-blue-700 transition-colors tracking-wider uppercase shadow-lg">
                Proceed to System
            </button>
        </form>
    </div>
</div>
@endsection