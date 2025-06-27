@php
    use Carbon\Carbon;
    @endphp
@extends('layouts.main')

@section('container')
<div class="rounded-lg bg-white p-6 shadow-lg grid grid-cols-2">
    <div class="flex flex-col gap-6">
        <div>
            <label class="block text-md font-bold">Nama Obat</label>
            <p class="mt-1 text-gray-600">{{ $batch->drug()->name }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Produsen</label>
            <p class="mt-1 text-gray-600">{{ $batch->drug()->manufacture()->name }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Tanggal Expired</label>
            <p class="mt-1 text-gray-600">{{ Carbon::parse($batch->expired)->translatedFormat('j F Y') }}</p>
        </div>
        <div>
            <label class="block text-md font-bold">Tanggal Diterima</label>
            <p class="mt-1 text-gray-600">{{ Carbon::parse($batch->created_at)->translatedFormat('j F Y') }}</p>
        </div>
    </div>
    <form id="create-retur-form" method="POST" action="{{ route('clinic.retur',$batch->id) }}" class="inline">
        @csrf
    <div class="flex flex-col gap-6">
        <div>
            <label class="block text-md font-bold">Vendor Pengirim</label>
            <p class="mt-1 text-gray-600">Klinik Simbat</p>
        </div>
        <div class="flex">
            <input type="number" id="quantity" name="quantity" class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0">
            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                pcs
            </span>
        </div>
        <textarea name="reason" rows="4" class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 " placeholder="Tuliskan alasan..."></textarea>
        <div class="flex justify-end">
            <button class="py-1 px-4 rounded-md bg-orange-500 hover:bg-orange-600 text-white">Retur</button>
        </div>
    </div>
</form>
</div>
<script>
    document.getElementById('create-retur-form').addEventListener('submit',function(e){
                e.preventDefault()
                showModal('save','create-retur-form')
            })
</script>
@endsection