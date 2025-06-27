@extends('layouts.main')

@section('container')
    <div class="container mx-auto">
        <div class="bg-white shadow-md rounded-lg py-12 px-6">
            <form id="edit-drug-form" action="{{ route('master.drug.update', $drug->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-6 gap-6">
                    <div class="flex flex-wrap col-span-4">
                        <div class="flex w-full mb-4">
                            <label for="nama_obat" class="w-1/4 text-gray-700">Nama Obat</label>
                            <h1 class="w-3/4">{{ $drug->name }}</h1>
                        </div>
                        <div class="flex w-full mb-4">
                            <label for="category_id" name="category_id" class="w-1/4 text-gray-700">Kategori Obat</label>
                            <h1 class="w-3/4">{{ $drug->category()->name }}</h1>
                        </div>
                        <div class="flex w-full mb-4">
                            <label for="variant_id" class="w-1/4 text-gray-700">Jenis Obat</label>
                            <h1 class="w-3/4">{{ $drug->variant()->name }}</h1>
                        </div>
                        <div class="flex w-full mb-4">
                            <label for="manufacture_id" class="w-1/4 text-gray-700">Produsen Obat</label>
                            <h1 class="w-3/4">{{ $drug->manufacture()->name }}</h1>
                        </div>
                        <div class="flex w-full mb-4">
                            <label for="maximum_capacity" class="w-1/4 text-gray-700">PKMa</label>
                            <input type="number" value="{{ $drug->maximum_capacity }}" id="maximum_capacity"
                                name="maximum_capacity" class="border border-gray-300 rounded p-2 w-3/4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Inputkan maksimum PKMa">
                        </div>
                        <div class="flex w-full mb-4">
                            <label for="minimum_capacity" class="w-1/4 text-gray-700">PKMi</label>
                            <input type="number" value="{{ $drug->minimum_capacity }}" id="minimum_capacity"
                                name="minimum_capacity" class="border border-gray-300 rounded p-2 w-3/4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Inputkan minimum PKMi">
                        </div>
                        <table class="w-full">
                            <tbody>
                                <tr>
                                    <td rowspan="3" class="w-48">Konversi</td>
                                    <td class="py-2 pe-24 pl-2">
                                        <div class="flex">
                                            <input value="{{ $drug->pack_quantity }}" type="number" id="pack_quantity"
                                                name="pack_quantity"
                                                class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="0">
                                            <span
                                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                                pack/box
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2 pe-24 pl-2">
                                        <div class="flex">
                                            <input value="{{ $drug->pack_margin }}" type="number" id="pack_margin"
                                                name="pack_margin"
                                                class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Margin">
                                            <span
                                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                                %
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2 pe-24 pl-2">
                                        <div class="flex">
                                            <input value="{{ $drug->piece_quantity }}" type="number" id="piece_quantity"
                                                name="piece_quantity"
                                                class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="0">
                                            <span
                                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                                pcs/pack
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2 pe-24 pl-2">
                                        <div class="flex">
                                            <input value="{{ $drug->piece_margin }}" type="number" id="piece_margin"
                                                name="piece_margin"
                                                class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Margin">
                                            <span
                                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                                %
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2 pe-24 pl-2">
                                        <span class="text-xs italic text-gray-400">Netto</span>
                                        <div class="flex">
                                            <input disabled value="{{ $drug->piece_netto }}" type="number"
                                                id="piece_netto" name="piece_netto"
                                                class="rounded-none rounded-s-lg text-gray-900 bg-gray-200 border border-gray-300 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Margin">
                                            <select disabled id="piece_unit" name="piece_unit"
                                                class="border border-gray-300 rounded-e-lg p-2 w-2/5 text-gray-900 bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option {{ $drug->piece_unit == 'ml' ? 'selected' : '' }} value="ml">ml
                                                </option>
                                                <option {{ $drug->piece_unit == 'mg' ? 'selected' : '' }} value="mg">mg
                                                </option>
                                                <option {{ $drug->piece_unit == 'butir' ? 'selected' : '' }}
                                                    value="butir">butir
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-white border rounded-lg p-6 shadow-sm w-full h-max col-span-2">
                        <div class="flex justify-between items-center">
                            <label for="last_price" class="text-gray-700 mr-2">Harga</label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                                    Rp
                                </span>
                                <input value="{{ $drug->last_price }}" type="number" id="last_price" name="last_price"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-40 text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Inputkan harga">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                    / pcs
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-6">
                            <label for="last_discount" class="text-gray-700 mr-2">Diskon</label>
                            <div class="flex">
                                <input value="{{ $drug->last_discount }}" type="number" id="last_discount"
                                    name="last_discount"
                                    class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block flex-1 min-w-0 w-full text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Inputkan discount">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                    %
                                </span>
                            </div>
                        </div>
                        <button type="submit"
                            class="bg-blue-500 w-full mt-6 text-white rounded hover:bg-blue-700 px-6 py-2">Simpan</button>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <button type="submit"
                        class="bg-blue-500 text-white rounded hover:bg-blue-700 px-6 py-2">Simpan</button>
                </div>
            </form>
        </div>
        <h2 class="text-2xl font-bold text-gray-700 mt-8">Repackaging</h2>

        <div class="bg-white shadow-md rounded-lg p-6 mt-4">
            <form id="create-repack-form" action="{{ route('master.drug.repack.store', $drug->id) }}" method="POST">
                @csrf
                <div class="flex items-center mb-4 gap-3">
                    <div class="flex">
                        <input type="number" id="quantity" name="quantity"
                            class="rounded-none rounded-s-lg text-gray-900 bg-gray-50 border border-gray-300 block flex-1 w-1/5 text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Jumlah">
                        <select id="piece_unit" name="piece_unit"
                            class="border border-gray-300 rounded-e-lg p-2 text-gray-900 bg-gray-50">
                            <option>Satuan</option>
                            <option value="{{ $drug->piece_unit }}">{{ $drug->piece_unit }}</option>
                            <option value="pcs">pcs</option>
                        </select>
                    </div>
                    <div class="flex">
                        <input type="number" id="margin" name="margin"
                            class="rounded-none rounded-s-lg bg-gray-50 border border-gray-300 text-gray-900 block w-1/12 flex-1 text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Margin">
                        <span
                            class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                            %
                        </span>
                    </div>
                    <button class="bg-blue-500 text-white rounded hover:bg-blue-700 px-4 py-2 w-28">Tambah</button>
                </div>
            </form>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full text-sm text-center">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-3 px-6">No</th>
                            <th class="py-3 px-6">Nama</th>
                            <th class="py-3 px-6">Harga</th>
                            <th class="py-3 px-6">Margin</th>
                            <th class="py-3 px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach ($repacks as $number => $item)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6">{{ $number + 1 }}</td>
                                <td class="py-3 px-6 text-left">{{ $item->name }}</td>
                                <td class="py-3 px-6 text-left">{{ 'Rp ' . number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-6">{{ $item->margin }}%</td>
                                <td class="py-3 px-6">
                                    <form id="delete-repack-form-{{ $item->id }}" action="{{ route('master.drug.repack.destroy', [$drug->id, $item->id]) }}"
                                        method="post">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button onclick="showModal('delete','delete-repack-form-{{ $item->id }}')"
                                        class="bg-red-500 text-white text-sm px-2 py-2 rounded-lg shadow hover:bg-red-600 transition-colors duration-200">
                                        <svg width="20" height="21" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z"
                                                fill="white" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
                <script>
            document.getElementById('edit-drug-form').addEventListener('submit',function(e){
                e.preventDefault()
                showModal('save','edit-drug-form')
            })
            document.getElementById('create-repack-form').addEventListener('submit',function(e){
                e.preventDefault()
                
                // Get form values
                const quantity = document.getElementById('quantity').value.trim();
                const margin = document.getElementById('margin').value.trim();
                
                // Check if any field is empty
                if (!quantity) {
                    showToastError('Jumlah harus diisi');
                    return;
                }
                
                if (!margin) {
                    showToastError('Margin harus diisi');
                    return;
                }
                
                // If validation passes, show modal
                showModal('save','create-repack-form')
            })
            
            function showToastError(message) {
                const toast = document.createElement('div');
                toast.id = 'toast-success';
                toast.className = 'fixed right-5 top-5 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow light:bg-gray-800 light:text-gray-400';
                toast.innerHTML = `
                    <div class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-500 light:bg-red-800 light:text-green-200">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                        </svg>
                    </div>
                    <div class="ml-3 text-sm font-normal">${message}</div>
                    <button type="button" onclick="this.parentElement.remove()" class="-mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 light:bg-gray-800 light:text-gray-500 light:hover:bg-gray-700 light:hover:text-white">
                        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                `;
                document.body.appendChild(toast);
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 3000);
            }
        </script>     
@endsection
