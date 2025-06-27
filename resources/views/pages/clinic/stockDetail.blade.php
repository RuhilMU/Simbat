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
    <div class="bg-white shadow-md rounded-lg mb-6">
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
                    <tr>
                        <td class="py-2">Sisa</td>
                        <td class="py-2">{{ $stock->quantity / $drug->piece_netto }} pcs</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <h2 class="text-xl font-semibold my-6">STOK OBAT</h2>
        <!-- Table Stok Obat -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">No</th>
                        <th class="border p-2">Nama Packaging Obat</th>
                        <th class="border p-2">Margin</th>
                        <th class="border p-2">Stok Konversi</th>
                        <th class="border p-2">Harga Jual</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($drug->repacks() as $number => $item)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-center">{{ $number + 1 }}</td>
                            <td class="py-3 px-6 text-center">{{ $item->name }}</td>
                            <td class="py-3 px-6 text-center">{{ $item->margin }}%</td>
                            <td class="py-3 px-6 text-center">{{ floor($stock->quantity / $item->quantity) }}</td>
                            <td class="py-3 px-6 text-center">{{ 'Rp ' . number_format($item->price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    <h2 class="text-xl font-semibold font-inter my-6 mt-4">KATEGORI BERDASARKAN EXP DATE</h2>
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                    <tr class="bg-gray-200 text-sm leading-normal">
                        <th class="border p-2">No</th>
                        <th class="border p-2">Waktu Expired Obat</th>
                        <th class="border p-2">Jumlah</th>
                        <th class="border p-2">Action</th>
                    </tr>
            </thead>
            <tbody class="text-gray-700 text-sm font-light">
                {{-- {{ dd($details) }} --}}
                @foreach ($details as $number=> $item)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-center">{{ $number+1 }}</td>
                    <td class="py-3 px-6 text-center">{{ Carbon::parse($item->expired)->translatedFormat('j F Y') }}</td>
                    <td class="py-3 px-6 text-center">{{ floor($item->stock/$item->drug()->piece_netto) }}</td>
                    <td class="py-3 px-6 text-center">
                    <div class="flex space-x-2 justify-center items-center">
                                    <a href="{{ route('clinic.retur', $item->id) }}"
                                        class="p-2 rounded-lg shadow bg-yellow-300 hover:bg-yellow-500 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M13.414 6.00002L15.243 7.82802L13.828 9.24302L9.586 5.00002L13.828 0.757019L15.243 2.17202L13.414 4.00002H16C17.3261 4.00002 18.5979 4.5268 19.5355 5.46449C20.4732 6.40217 21 7.67394 21 9.00002V13H19V9.00002C19 8.20437 18.6839 7.44131 18.1213 6.8787C17.5587 6.31609 16.7956 6.00002 16 6.00002H13.414ZM15 11V21C15 21.2652 14.8946 21.5196 14.7071 21.7071C14.5196 21.8947 14.2652 22 14 22H4C3.73478 22 3.48043 21.8947 3.29289 21.7071C3.10536 21.5196 3 21.2652 3 21V11C3 10.7348 3.10536 10.4804 3.29289 10.2929C3.48043 10.1054 3.73478 10 4 10H14C14.2652 10 14.5196 10.1054 14.7071 10.2929C14.8946 10.4804 15 10.7348 15 11ZM13 12H5V20H13V12Z"
                                                fill="white" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('clinic.trash', $item->id) }}"
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
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div style="overflow-x: auto;"> <!-- Tambahkan style inline untuk scroll horizontal -->
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-200 text-sm leading-normal">
                        <th class="border p-2">No</th>
                        <th class="border p-2">Jenis Packaging Obat</th>
                        <th class="border p-2">Margin</th>
                        <th class="border p-2">Harga</th>
                        <th class="border p-2">Jumlah</th>
                        <th class="border p-2">Status</th>
                        <th class="border p-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm font-light">
                    @foreach ($transactions as $number => $item)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-center">{{ $number+1 }}</td>
                        <td class="py-3 px-6 text-center">{{ $item->name }}</td>
                        <td class="py-3 px-6 text-center">{{ $item->margin }}%</td>
                        <td class="py-3 px-6 text-center">{{ 'Rp ' . number_format($item->piece_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-center">{{ $item->quantity }}</td>
                        <td class="py-3 px-6 text-center">{{ getStatus($item->transaction->variant) }}</td>
                        <td class="py-3 px-6 text-center">{{ 'Rp ' . number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                        
                    @endforeach
                </tbody>
            </table>
            <div class="p-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
