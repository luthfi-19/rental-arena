@extends('layouts.app')
@section('title', 'Kasir F&B')

@section('content')

<div class="flex h-[calc(100vh-65px)]">
    <div class="w-[70%] p-6 overflow-y-auto no-scrollbar">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4 border-b border-gray-800 pb-2">Katalog Menu</h2>
        <div class="grid grid-cols-4 gap-4">
            @foreach($menus as $m)
            <div onclick="addToCart({{ $m->id_menu }}, '{{ $m->nama_menu }}', {{ $m->harga }})" class="bg-arena-card border border-gray-800 rounded-lg overflow-hidden cursor-pointer hover:border-electric-blue hover:glow-active transition-all group">
                @if($m->gambar)
                    <img src="{{ asset('storage/'.$m->gambar) }}" class="w-full h-28 object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                @else
                    <div class="w-full h-28 bg-gray-900 flex items-center justify-center text-gray-600 text-xs">No Image</div>
                @endif
                <div class="p-3">
                    <span class="text-[10px] text-electric-blue uppercase font-bold tracking-wider">{{ $m->kategori }}</span>
                    <h3 class="text-white font-medium text-sm leading-tight mt-0.5 truncate">{{ $m->nama_menu }}</h3>
                    <p class="text-green-400 font-mono text-xs mt-1">Rp {{ number_format($m->harga, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="w-[30%] bg-arena-card border-l border-gray-800 p-6 flex flex-col justify-between">
        <div>
            <h2 class="text-sm font-bold uppercase tracking-widest text-white mb-4 flex justify-between">
                Current Order <span class="bg-electric-blue text-[10px] px-2 py-0.5 rounded" id="cartCount">0 Item</span>
            </h2>
            <div id="cartItems" class="space-y-3 max-h-[50vh] overflow-y-auto pr-2 no-scrollbar">
                <p class="text-xs text-gray-500 italic text-center mt-10">Keranjang masih kosong, tap menu untuk menambahkan.</p>
            </div>
        </div>

        <div class="mt-6 border-t border-gray-800 pt-4">
            <div class="flex justify-between items-end mb-4">
                <span class="text-xs uppercase text-gray-400 font-bold tracking-wider">Total Pembayaran</span>
                <span class="text-2xl font-bold text-green-400" id="cartTotalUI">Rp 0</span>
            </div>

            <form action="{{ route('kasir.fnb.checkout') }}" method="POST" id="checkoutForm">
                @csrf
                <input type="hidden" name="cart_data" id="cart_data">
                
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Hubungkan ke Sesi (Opsional)</label>
                    <select name="id_sewa" class="w-full bg-arena-bg border border-gray-700 rounded px-3 py-2 text-white text-xs focus:border-electric-blue outline-none">
                        <option value="">-- Walk-in / Order Terpisah --</option>
                        @foreach($sesiAktif as $sesi)
                            <option value="{{ $sesi->id_sewa }}">[{{ $sesi->perangkat->kode_unit }}] {{ $sesi->nama_pelanggan ?? 'Guest' }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" id="btnCheckout" disabled class="w-full bg-electric-blue text-white font-bold py-4 rounded text-sm uppercase tracking-widest shadow-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Process Checkout
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = {};

    function addToCart(id, nama, harga) {
        if (cart[id]) {
            cart[id].qty += 1;
            cart[id].subtotal = cart[id].qty * cart[id].harga;
        } else {
            cart[id] = { id_menu: id, nama: nama, harga: harga, qty: 1, subtotal: harga };
        }
        renderCart();
    }

    function minQty(id) {
        if (cart[id].qty > 1) {
            cart[id].qty -= 1;
            cart[id].subtotal = cart[id].qty * cart[id].harga;
        } else {
            delete cart[id];
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const inputData = document.getElementById('cart_data');
        const totalUI = document.getElementById('cartTotalUI');
        const btnCheckout = document.getElementById('btnCheckout');
        const countUI = document.getElementById('cartCount');

        container.innerHTML = '';
        let total = 0;
        let itemsArr = Object.values(cart);
        
        if (itemsArr.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-500 italic text-center mt-10">Keranjang masih kosong, tap menu untuk menambahkan.</p>';
            totalUI.innerText = 'Rp 0';
            countUI.innerText = '0 Item';
            inputData.value = '';
            btnCheckout.disabled = true;
            return;
        }

        itemsArr.forEach(item => {
            total += item.subtotal;
            container.innerHTML += `
                <div class="bg-arena-bg border border-gray-700 p-3 rounded flex justify-between items-center">
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase">${item.nama}</h4>
                        <p class="text-[10px] text-gray-400">Rp ${item.harga.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="minQty(${item.id_menu})" class="w-6 h-6 rounded bg-gray-700 text-white font-bold hover:bg-critical-red transition">-</button>
                        <span class="text-sm font-bold text-white w-4 text-center">${item.qty}</span>
                        <button onclick="addToCart(${item.id_menu}, '${item.nama}', ${item.harga})" class="w-6 h-6 rounded bg-gray-700 text-white font-bold hover:bg-electric-blue transition">+</button>
                    </div>
                </div>
            `;
        });

        totalUI.innerText = 'Rp ' + total.toLocaleString('id-ID');
        countUI.innerText = itemsArr.length + ' Item';
        inputData.value = JSON.stringify(itemsArr);
        btnCheckout.disabled = false;
    }
</script>
@endsection