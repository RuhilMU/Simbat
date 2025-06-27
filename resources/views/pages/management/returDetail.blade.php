@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')

@section('container')
<div class="rounded-lg bg-white p-6 shadow-lg grid grid-cols-2">
    <div class="flex flex-col gap-6">
        <div>
            <label class="block text-md font-bold">Nama Obat</label>
            <p class="mt-1 text-gray-600">{{ $retur->drug()->name }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Produsen</label>
            <p class="mt-1 text-gray-600">{{ $retur->drug()->manufacture()->name }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Tanggal Expired</label>
            <p class="mt-1 text-gray-600">{{ Carbon::parse($retur->detail()->expired)->translatedFormat('j F Y') }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Tanggal Retur</label>
            <p class="mt-1 text-gray-600">{{ Carbon::parse($retur->created_at)->translatedFormat('j F Y') }}</p>
        </div>
    </div>
    <div class="flex flex-col gap-6">
        <div>
            <label class="block text-md font-bold">Kode Transaksi</label>
            <p class="mt-1 text-gray-600">{{ $retur->transaction()->code }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Vendor Pengirim</label>
            <p class="mt-1 text-gray-600">{{ $retur->transaction()->vendor()->name }}</p>
        </div>
        <div class="flex">
            <input value="{{ $retur->quantity/$retur->drug()->piece_netto }}" disabled class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5" placeholder="0">
            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                pcs
            </span>
        </div>
        <textarea disabled name="inputReason" rows="4" class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ $retur->reason }}</textarea>
        
        @if ($retur->status == "Belum Kembali")
            <div class="justify-end flex">
                <form id="retur-receive-form" action="{{ route('management.retur.pay', $retur->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="new_expired_date" id="new_expired_date">
                </form>
                <button onclick="showExpDateModal()" class="mt-3 bg-blue-500 hover:bg-blue-700 py-1 px-4 rounded-md text-white">Diterima</button>
            </div>
        @endif
    </div>
</div>

<!-- Modal Exp Date -->
<div id="expDateModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeModal()">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
        <div class="text-center">
            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Masukkan Tanggal Expired Terbaru</h3>
            <p class="text-sm text-gray-500 mb-5">Silakan pilih tanggal expired terbaru sebelum melanjutkan.</p>
            <input type="date" id="expired_date_input" 
                min="{{ Carbon::now()->addDay()->format('Y-m-d') }}"
                class="rounded-sm px-2 py-1 ring-2 ring-gray-500 mb-6" required />
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                Batal
            </button>
            <button onclick="submitExpDate()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    function showExpDateModal() {
        document.getElementById('expDateModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('expDateModal').classList.add('hidden');
    }

    function submitExpDate() {
        const newExpiredDate = document.getElementById('expired_date_input').value;
        
        if (!newExpiredDate) {
            alert('Mohon pilih tanggal expired terbaru');
            return;
        }

        document.getElementById('new_expired_date').value = newExpiredDate;
        document.getElementById('expDateModal').classList.add('hidden');
        showModal('save', 'retur-receive-form');
    }
</script>
@endsection
