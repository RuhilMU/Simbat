@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')

@section('container')
    <div class="rounded-lg bg-white p-6 shadow-lg grid grid-cols-2">
        <div class="flex flex-col gap-6">
            <div>
                <label class="block font-bold">Nama Obat</label>
                <p class="mt-1 text-gray-600">{{ $batch->drug()->name }}</p>
            </div>
            <div>
                <label class="block font-bold">Produsen</label>
                <p class="mt-1 text-gray-600">{{ $batch->drug()->manufacture()->name }}</p>
            </div>
            <div>
                <label class="block font-bold">Tanggal Expired</label>
                <p class="mt-1 text-gray-600">{{ Carbon::parse($batch->expired)->translatedFormat('j F Y') }}</p>
            </div>
            <div>
                <label class="block font-bold">Tanggal Diterima</label>
                <p class="mt-1 text-gray-600">{{ Carbon::parse($batch->created_at)->translatedFormat('j F Y') }}</p>
            </div>
        </div>
        <form id="create-trash-form" method="POST" action="{{ route('clinic.trash', $batch->id) }}" class="inline">
            @csrf
        <div class="flex flex-col gap-6">
            <div>
                <label class="block font-bold">Vendor Pengirim</label>
                <p class="mt-1 text-gray-600">Klinik Simbat</p>
            </div>
            <div class="flex">
                <input type="number" id="quantity" name="quantity"
                    class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="0">
                    <span
                    class="inline-flex items-center px-3 text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                    pcs
                </span>
            </div>
            <textarea name="reason" rows="4"
            class="block p-3 w-full text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Tuliskan alasan..."></textarea>
            <div class="flex justify-end">
                <button class="py-1 px-4 rounded-md bg-red-500 hover:bg-red-700 text-white">Buang</button>
            </div>
        </form>
        </div>
    </div>
    <script>
        document.getElementById('create-trash-form').addEventListener('submit',function(e){
                e.preventDefault()
                showModal('save','create-trash-form')
            })
    </script>
@endsection
