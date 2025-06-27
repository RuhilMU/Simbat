@php
    use Carbon\Carbon;
    function getStatus(string $variant)
    {
        match ($variant) {
            'LPB' => ($status = 'Masuk'),
            'LPK' => ($status = 'Klinik'),
            'Checkout' => ($status = 'Keluar'),
            'Trash' => ($status = 'Buang'),
            'Retur' => ($status = 'Retur'),
        };
        return $status;
    }
@endphp
@extends('layouts.main')
@section('container')
<div class="flex flex-1 justify-end mr-8">
    <button onclick="printModal()" class="rounded-lg bg-yellow-500 hover:bg-yellow-600 px-4 py-1 text-white">Cetak</button>
</div>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="bg-white shadow-md rounded-lg">
            <div class="bg-gray-200 p-4 rounded-t-lg">
                <h2 class="font-semibold">Detail Obat</h2>
            </div>
            <div class="p-4">
                <table class="w-full">
                    <tbody>
                        <tr class="border-b">
                            <td class="py-2">Nama</td>
                            <td class="py-2">{{ $drug->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Kode Obat</td>
                            <td class="py-2">{{ $drug->code }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Jenis</td>
                            <td class="py-2">{{ $drug->variant()->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Kategori</td>
                            <td class="py-2">{{ $drug->category()->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Produsen</td>
                            <td class="py-2">{{ $drug->manufacture()->name }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2">Sisa</td>
                            <td class="py-2">
                                {{ floor($totalStock / $drug->piece_netto) }} pcs
                                @if($warehouseStock && $warehouseStock->quantity >= 0)
                                    <br><span class="text-sm text-gray-600">(Gudang: {{ floor($warehouseStock->quantity / $drug->piece_netto) }} pcs)</span>
                                @endif
                                @if($clinicStock && $clinicStock->quantity >= 0)
                                    <br><span class="text-sm text-gray-600">(Klinik: {{ floor($clinicStock->quantity / $drug->piece_netto) }} pcs)</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <h2 class="text-xl font-semibold my-6">STOK OBAT</h2>
        <!-- Table Stok Obat -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">No</th>
                        <th class="border p-2">Nama Packaging Obat</th>
                        <th class="border p-2">Margin</th>
                        <th class="border p-2">Stok konversi</th>
                        <th class="border p-2">Harga Jual</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($drug->repacks() as $number => $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6">{{ $number + 1 }}</td>
                            <td class="py-3 px-6 text-left">{{ $item->name }}</td>
                            <td class="py-3 px-6">{{ $item->margin }}%</td>
                            <td class="py-3 px-6">{{ floor($totalStock / $item->quantity) }}</td>
                            <td class="py-3 px-6">{{ 'Rp ' . number_format($item->price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <h2 class="text-xl font-semibold my-6">KATEGORI BERDASARKAN EXP DATE</h2>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">No</th>
                        <th class="border p-2">Waktu Expired Obat</th>
                        <th class="border p-2">Jumlah</th>
                        <th class="border p-2">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($details as $number => $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6">{{ $number + 1 }}</td>
                            <td class="py-3 px-6">
                                {{ Carbon::parse($item->expired)->translatedFormat('j F Y') }}</td>
                            <td class="py-3 px-6">{{ floor($item->stock / $item->drug()->piece_netto) }}</td>
                            <td class="py-3 px-6">
                                <div class="flex space-x-2 justify-center items-center">
                                    <a href="{{ route('inventory.retur', $item->id) }}"
                                        class="p-2 rounded-lg shadow bg-yellow-300 hover:bg-yellow-500 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.414 6.00002L15.243 7.82802L13.828 9.24302L9.586 5.00002L13.828 0.757019L15.243 2.17202L13.414 4.00002H16C17.3261 4.00002 18.5979 4.5268 19.5355 5.46449C20.4732 6.40217 21 7.67394 21 9.00002V13H19V9.00002C19 8.20437 18.6839 7.44131 18.1213 6.8787C17.5587 6.31609 16.7956 6.00002 16 6.00002H13.414ZM15 11V21C15 21.2652 14.8946 21.5196 14.7071 21.7071C14.5196 21.8947 14.2652 22 14 22H4C3.73478 22 3.48043 21.8947 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V11C3 10.7348 3.10536 10.4804 3.29289 10.2929C3.48043 10.1054 3.73478 10 4 10H14C14.2652 10 14.5196 10.1054 14.7071 10.2929C14.8946 10.4804 15 10.7348 15 11ZM13 12H5V20H13V12Z"
                                                fill="white" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('inventory.trash', $item->id) }}"
                                        class="p-2 rounded-lg shadow bg-red-600 hover:bg-red-800 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 20 21" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z"
                                                fill="white" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-6">
                {{ $details->links() }}
            </div>
        </div>
        <h2 class="text-xl font-semibold my-6">HISTORI TRANSAKSI</h2>
        <div class="flex items-center justify-between w-full my-6 mt-8">
            <form action="" method="GET" class="flex w-auto flex-row justify-between gap-3">
                <input class="rounded-sm px-2 py-1 ring-2 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    type="date" name="start" value="{{ request('start') }}" />
                <h1 class="text-lg font-inter text-gray-800">sampai</h1>
                <input class="rounded-sm px-2 py-1 ring-2 ring-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    type="date" name="end" value="{{ request('end') }}" />
                <button class="rounded-2xl bg-blue-500 px-3 font-bold text-sm font-inter text-white hover:bg-blue-600"
                    type="submit">
                    TERAPKAN
                </button>
                @if(request('start') || request('end'))
                    <a href="{{ route('report.drugs.show', $drug->id) }}" 
                        class="rounded-2xl bg-gray-500 px-3 font-bold text-sm font-inter text-white hover:bg-gray-600">
                        RESET
                    </a>
                @endif
            </form>
        </div>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div style="overflow-x: auto;"> <!-- Tambahkan style inline untuk scroll horizontal -->
                <table class="min-w-full text-sm text-center">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Jenis Packaging Obat</th>
                            <th class="border p-2">Margin</th>
                            <th class="border p-2">Harga</th>
                            <th class="border p-2">Jumlah</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 font-light">
                        @foreach ($transactions as $number => $item)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6">{{ $number + 1 }}</td>
                                <td class="py-3 px-6">{{ $item->name }}</td>
                                <td class="py-3 px-6">{{ $item->margin }}%</td>
                                <td class="py-3 px-6">
                                    {{ 'Rp ' . number_format($item->piece_price, 0, ',', '.') }}</td>
                                <td class="py-3 px-6">{{ $item->quantity }}</td>
                                <td class="py-3 px-6">{{ getStatus($item->transaction->variant) }}</td>
                                <td class="py-3 px-6">
                                    {{ 'Rp ' . number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-6">
                    {{ $transactions->links() }}
                </div>
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
        function printModal() {
        document.getElementById('printModal')?.classList.remove('hidden');
        }

        function closePrintModal() {
            let modal = document.getElementById('printModal');
            if (modal) {
                modal.classList.add('hidden');
            } else {
                console.warn("Modal tidak ditemukan!");
            }
        }

        function exportToExcel() {
            closePrintModal(); // tetap tutup modal
            const drugId = {{ $drug->id }};
            window.location.href = `/drug/${drugId}/export`;
        }

        function submitModal() {
            closePrintModal(); // tutup modal
            const drugId = {{ $drug->id }};
            window.location.href = `/drug/${drugId}/export-pdf`;
        }


            document.getElementById('printButton').onclick = function() {
                document.getElementById('printOptions').classList.toggle('invisible');
            };
            document.getElementById('confirmPrint').onclick = function() {
                const format = document.getElementById('format').value;
                if (format === 'pdf') {
                    alert('Mencetak dalam format PDF...');
                } else if (format === 'excel') {
                    alert('Mencetak dalam format Excel...');
                }
                document.getElementById('printOptions').classList.add('invisible');
            };
        </script>
    @endsection

