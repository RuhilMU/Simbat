@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')
@section('container')
<div class="mb-4 flex items-center">
            <div class="flex flex-1 justify-end mr-5">
                <button onclick="uploadModal()" class="rounded-lg bg-yellow-300 hover:bg-yellow-500 px-4 py-1 text-white">Cetak</button>
            </div>
        </div>
    <div class="rounded-lg bg-white p-6 shadow-lg">
        <div class="mb-4 flex items-center">
            <div class="flex-1 mr-5">
                <h2 class="text-xl font-bold text-left">{{ App\Models\Profile::first()->name }}</h2>
                <h2 class="text-sm text-left">{{ App\Models\Profile::first()->address }}</h2>
                <h2 class="text-sm text-left">{{ App\Models\Profile::first()->phone }}</h2>
            </div>
            <div class="flex-[1.5] flex flex-col  items-center justify-center">
                <div class="flex items-center">
                    <span class="mr-2 font-normal text-black">No. LPB :</span>
                    <span>{{ $transaction->code }}</span>
                </div>
                <h1 class="text-center text-2xl font-bold">Invoice</h1>
            </div>
            <div class="flex-1 ml-10">
                <p class="text-sm mb-4 text-left">Tanggal: {{ Carbon::parse($transaction->created_at)->translatedFormat('j F Y') }}</p>
            </div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white shadow-md mt-8">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="py-4 w-2 px-4">No</th>
                    <th class="py-4 px-2">Nama Item</th>
                    <th class="py-4 w-36">Jumlah</th>
                    <th class="py-4 w-36">Harga Satuan</th>
                    <th class="py-4 w-36">Total</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($transaction->details() as $number=> $item)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="">{{ $number+1 }}</td>
                        <td class="px-6 py-3 text-left">{{ $item->name }}</td>
                        <td class="">{{ $item->quantity }}</td>
                        <td class="">{{ 'Rp ' . number_format($item->piece_price, 0, ',', '.') }}</td>
                        <td class="">{{ 'Rp ' . number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                        
                    @endforeach
                </tbody>
            </table>
            <div class="flex justify-end p-4">
                <div class="grid grid-cols-2 gap-2 w-max">
    
                    <h1 class="text-end">Jumlah pembelian: </h1><span id="total" class="font-bold">{{ 'Rp ' . number_format($transaction->income+$transaction->discount, 0, ',', '.') }}</span>
                    <h1 class="text-end">Discount: </h1><span id="totalDisc" class="font-bold">{{ 'Rp ' . number_format($transaction->discount, 0, ',', '.') }}</span>
                    <h1 class="text-end">Total dibayar: </h1><span id="totalPay" class="font-bold">{{ 'Rp ' . number_format($transaction->income, 0, ',', '.') }}</span>
        
                </div>

            </div>
        </div>
    </div>
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
            <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeUploadModal()">
                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="text-center">
                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Apa format file yang ingin Anda simpan?</h3>
                <p class="text-sm text-gray-500 mb-5">Pilihlah salah satu format file!</p>
            </div>
            <div class="flex justify-center space-x-4">
                <button data-transaction-id="{{ $transaction->id }}" onclick="closeUploadModal(this.getAttribute('data-transaction-id'))"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-blue-500 hover:text-white focus:outline-none">
                    Excel
                </button>

                <button data-transaction-id="{{ $transaction->id }}" onclick="submitModal(this.getAttribute('data-transaction-id'))" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-blue-500 hover:text-white focus:outline-none">
                    PDF
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function uploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }
        function closeUploadModal(transaction_id) {
            document.getElementById('uploadModal').classList.add('hidden');

            if (!transaction_id) {
                console.error("Transaction ID is missing!");
                return;
            }

            console.log("Download button clicked, transaction ID:", transaction_id); // Debugging

            // Redirect langsung ke endpoint Laravel
            window.location.href = `/checkout/export/${transaction_id}`;
        }

        function submitModal(transaction_id) {
            document.getElementById('uploadModal').classList.add('hidden');

            if (!transaction_id) {
                console.error("Transaction ID is missing!");
                return;
            }

            console.log("Download button clicked, transaction ID:", transaction_id); // Debugging

            // Redirect langsung ke endpoint Laravel
            window.location.href = `/checkout/generate-pdf/${transaction_id}`;
        }
    </script>
@endsection
