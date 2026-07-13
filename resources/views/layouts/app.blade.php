<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Arena Operational System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        'arena-bg': '#0D0D0F',
                        'arena-card': '#1A1B1E',
                        'electric-blue': '#004D98',
                        'critical-red': '#A50044',
                    }
                }
            }
        }
    </script>
    <style>
        .glow-active { box-shadow: 0 0 12px rgba(0, 77, 152, 0.4); border-color: #004D98; }
        .pulse-warn { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

        .glow-active { box-shadow: 0 0 12px rgba(0, 77, 152, 0.4); border-color: #004D98; }
        .pulse-warn { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
        
        /* TAMBAHKAN KODE INI UNTUK HIDE SCROLLBAR */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-arena-bg text-gray-100 font-sans min-h-screen antialiased">
    
    @auth
    @php
        // Hitung perangkat yang perlu perhatian (< 10% sisa umur ATAU status maintenance)
        $alertCount = \App\Models\Perangkat::whereRaw('jam_terbang_total >= (ambang_batas_servis * 0.9)')
                        ->orWhere('status', 'maintenance')
                        ->count();
    @endphp
    
    <nav class="bg-arena-card border-b border-gray-800 px-6 py-4 flex justify-between items-center sticky top-0 z-40 shadow-md">
        <div class="flex items-center gap-6">
            <h1 class="text-lg font-bold tracking-wider text-white">ARENA SYSTEM</h1>
            <div class="hidden md:flex gap-4 text-xs font-bold uppercase tracking-wider">
                
                @if(auth()->user()->role === 'owner')
                    <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'text-electric-blue' : 'text-gray-400 hover:text-white' }}">Analytics</a>
                    <a href="{{ route('maintenance.index') }}" class="{{ request()->routeIs('maintenance.*') ? 'text-electric-blue' : 'text-gray-400 hover:text-white' }}">Maintenance</a>
                    <a href="{{ route('owner.fnb.index') }}" class="{{ request()->routeIs('owner.fnb.*') ? 'text-critical-red' : 'text-gray-400 hover:text-critical-red' }}">Admin F&B</a>
                    <a href="{{ route('owner.users.index') }}" class="{{ request()->routeIs('owner.users.*') ? 'text-critical-red' : 'text-gray-400 hover:text-critical-red' }}">Kelola Akun</a>
                @else
                    <a href="{{ route('kasir.dashboard') }}" class="{{ request()->routeIs('kasir.dashboard') ? 'text-electric-blue' : 'text-gray-400 hover:text-white' }}">Live Denah</a>
                    <a href="{{ route('kasir.fnb.index') }}" class="{{ request()->routeIs('kasir.fnb.*') ? 'text-electric-blue' : 'text-gray-400 hover:text-white' }}">POS Kasir</a>
                    <a href="{{ route('maintenance.index') }}" class="{{ request()->routeIs('maintenance.*') ? 'text-electric-blue' : 'text-gray-400 hover:text-white' }}">Maintenance</a>
                @endif

            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('maintenance.index') }}" class="relative group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($alertCount > 0)
                    <span class="absolute -top-1 -right-1 bg-critical-red text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-arena-bg animate-pulse">
                        {{ $alertCount }}
                    </span>
                @endif
            </a>

            <div class="border-l border-gray-700 pl-4 flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-white uppercase">{{ auth()->user()->nama }}</p>
                    <p class="text-[10px] text-gray-500 uppercase">{{ auth()->user()->role }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs uppercase tracking-wider text-gray-400 border border-gray-700 px-3 py-1.5 rounded hover:bg-critical-red hover:text-white transition-all">
                        Exit
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    @yield('content')
</body>
</html>