@php
use Carbon\Carbon;
@endphp
@extends('layouts.main')
@section('container')
<div class="rounded-lg bg-white p-6 shadow-lg">
    <div class="flex flex-1 justify-end mb-5">
        <button onclick="printModal()" class="rounded-lg bg-yellow-500 hover:bg-yellow-600 px-4 py-1 text-white">Cetak</button>
    </div>
    <div class="flex items-center justify-between w-full">
        <form action="{{ route('report.drugs.index') }}" method="GET" class="flex w-auto flex-row justify-between gap-3 ">
            <input class="rounded-sm px-2 py-1 ring-2 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   type="date" name="start" id="startDate" value="{{ $startDate ?? '' }}" />
            <h1 class="text-lg font-inter text-gray-800">sampai</h1>
            <input class="rounded-sm px-2 py-1 ring-2 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   type="date" name="end" id="endDate" value="{{ $endDate ?? '' }}" />
            <button class="rounded-2xl bg-blue-500 px-3 font-bold text-sm font-inter text-white hover:bg-blue-600"
                type="submit">
                TERAPKAN
            </button>
        </form>
            <form action="{{ route('report.drugs.index') }}" method="GET" class="flex">
                <input type="text" name="query" id="searchInput" placeholder="Search..." value="{{ request('query') }}"
                    class="rounded-full px-6 py-2 ring-2 ring-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-md mt-6">
        <table class="min-w-full text-sm text-center">
            <thead class="bg-gray-200">
                <th class="py-4">No</th>
                <th class="py-4">Kode Obat</th>
                <th class="py-4">Nama Obat</th>
                <th class="py-4">Stok</th>
                <th class="py-4">Expired Terdekat</th>
                <th class="py-4">Expired Terbaru</th>
                <th class="py-4">Action</th>
            </thead>

            <tbody id="suggestions">
                @foreach ($stocks as $number => $item)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3">{{ $number + 1 }}</td>
                    <td class="py-3">{{ $item->drug->code }}</td>
                    <td class="py-3 text-left">{{ $item->drug->name }}</td>
                    <td class="py-3">{{ floor($item->quantity/$item->drug->piece_netto) }} pcs</td>
                    <td class="py-3">{{ $item->oldest ? Carbon::parse($item->oldest)->translatedFormat('j F Y') : '-' }}</td>
                    <td class="py-3">{{ $item->latest ? Carbon::parse($item->latest)->translatedFormat('j F Y') : '-' }}</td>
                    <td class="flex justify-center py-3">
                        <a href="{{ route('report.drugs.show', $item->drug->id) }}"
                            class="rounded-md bg-blue-500 p-2 hover:bg-blue-600">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.99972 2.5C14.4931 2.5 18.2314 5.73333 19.0156 10C18.2322 14.2667 14.4931 17.5 9.99972 17.5C5.50639 17.5 1.76805 14.2667 0.983887 10C1.76722 5.73333 5.50639 2.5 9.99972 2.5ZM9.99972 15.8333C11.6993 15.833 13.3484 15.2557 14.6771 14.196C16.0058 13.1363 16.9355 11.6569 17.3139 10C16.9341 8.34442 16.0038 6.86667 14.6752 5.80835C13.3466 4.75004 11.6983 4.17377 9.99972 4.17377C8.30113 4.17377 6.65279 4.75004 5.3242 5.80835C3.9956 6.86667 3.06536 8.34442 2.68555 10C3.06397 11.6569 3.99361 13.1363 5.32234 14.196C6.65106 15.2557 8.30016 15.833 9.99972 15.8333V15.8333ZM9.99972 13.75C9.00516 13.75 8.05133 13.3549 7.34807 12.6516C6.64481 11.9484 6.24972 10.9946 6.24972 10C6.24972 9.00544 6.64481 8.05161 7.34807 7.34835C8.05133 6.64509 9.00516 6.25 9.99972 6.25C10.9943 6.25 11.9481 6.64509 12.6514 7.34835C13.3546 8.05161 13.7497 9.00544 13.7497 10C13.7497 10.9946 13.3546 11.9484 12.6514 12.6516C11.9481 13.3549 10.9943 13.75 9.99972 13.75ZM9.99972 12.0833C10.5523 12.0833 11.0822 11.8638 11.4729 11.4731C11.8636 11.0824 12.0831 10.5525 12.0831 10C12.0831 9.44747 11.8636 8.91756 11.4729 8.52686C11.0822 8.13616 10.5523 7.91667 9.99972 7.91667C9.44719 7.91667 8.91728 8.13616 8.52658 8.52686C8.13588 8.91756 7.91639 9.44747 7.91639 10C7.91639 10.5525 8.13588 11.0824 8.52658 11.4731C8.91728 11.8638 9.44719 12.0833 9.99972 12.0833Z"
                                    fill="white" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-6">
        {{ $stocks->appends(request()->query())->links() }}
    </div>
</div>
<div id="printModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="closePrintModal()">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
        <div class="text-center">
            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Apa format file yang ingin Anda simpan?</h3>
            <p class="text-sm text-gray-500 mb-5">Pilihlah salah satu format file!</p>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="exportToExcel()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-blue-500 hover:text-white focus:outline-none">
                Excel
            </button>
            <button onclick="submitModal()" type="button"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-blue-500 hover:text-white focus:outline-none">
                PDF
            </button>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        let url = '/report/drugs?query=' + searchValue;
        if (startDate) url += '&start=' + startDate;
        if (endDate) url += '&end=' + endDate;
        
        if (searchValue.length > 0) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newSuggestions = doc.getElementById('suggestions');
                    const newPagination = doc.querySelector('.p-6');
                    
                    document.getElementById('suggestions').innerHTML = newSuggestions.innerHTML;
                    document.querySelector('.p-6').innerHTML = newPagination.innerHTML;
                });
        } else {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newSuggestions = doc.getElementById('suggestions');
                    const newPagination = doc.querySelector('.p-6');
                    
                    document.getElementById('suggestions').innerHTML = newSuggestions.innerHTML;
                    document.querySelector('.p-6').innerHTML = newPagination.innerHTML;
                });
        }
    });

    function printModal() {
        document.getElementById('printModal').classList.remove('hidden');
    }

    function closePrintModal() {
        const modal = document.getElementById('printModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function exportToExcel() {
        closePrintModal();
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        let url = "/export-excel";
        if (startDate && endDate) {
            url += "?start=" + startDate + "&end=" + endDate;
        }
        window.location.href = url;
    }

    function submitModal() {
        closePrintModal();
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        let url = "/export-pdf";
        if (startDate && endDate) {
            url += "?start=" + startDate + "&end=" + endDate;
        }
        window.location.href = url;
    }
</script>
@endsection
