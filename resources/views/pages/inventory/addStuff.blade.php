@extends('layouts.main')
@section('container')
<div class="rounded-lg bg-white p-6 shadow-lg">
    <div class="justify-right mb-4 flex items-center">
    <button onclick="uploadModal()" class="mr-4 rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-arrow-up mr-2" viewBox="0 0 16 16">
        <path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
    </svg>
    Upload
</button>

        <a href="{{ route('export.template') }}" class=" rounded-lg bg-yellow-500 px-4 py-2 text-white hover:bg-yellow-600 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-download mr-2" viewBox="0 0 16 16">
  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
</svg>Template</a>
    </div>
    <div class="space-y-4">
        <form id="add-stuff-form" action="{{ route('inventory.inflows.store') }}" method="post" class="space-y-4">
            @csrf
            <input type="hidden" name="transaction">
            <input type="hidden" name="total">
            <input type="hidden" name="destination" value="warehouse">
            
            <div class="grid grid-cols-2  gap-4">
                <div>
                    <select name="vendor_id" class="w-full rounded border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('vendor_id') border-red-500 @enderror">
                        <option selected disabled>Inputkan vendor</option>
                        @foreach ($vendors as $item)
                        <option value="{{ $item->id }}" {{ old('vendor_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex">
                    <select name="method" class="w-full rounded-s border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('method') border-red-500 @enderror">
                        <option selected disabled>Bayar Langsung / Bayar Tempo</option>
                        <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>Bayar Langsung</option>
                        <option value="credit" {{ old('method') == 'credit' ? 'selected' : '' }}>Bayar Tempo</option>
                    </select>
                    <input name="due" type="date" class="w-full rounded-e border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('due') border-red-500 @enderror" value="{{ old('due') }}" />
                </div>
            </div>
        </form>
        <div class="grid grid-cols-3 gap-4">
            <input type="text" id="drugInput" name="drug" class="w-full rounded border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Inputkan nama obat" autocomplete="off">
            <ul id="suggestions" class="absolute mt-10 border border-gray-300 bg-white rounded hidden"></ul>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex">
                    <input name="quantity" type="number" class="w-full rounded-s border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Jumlah" />
                    <select name="unit" class="w-full rounded-e border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option class="pcs">pcs</option>
                        <option class="pack">pack</option>
                        <option class="box">box</option>
                    </select>
                </div>
                <input name="price" type="number" class="w-full rounded border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Harga Satuan" />
            </div>
            <div class="grid grid-cols-2 gap-12">
                <div class="flex">
                    <a class="w-full rounded-s border border-gray-300 p-2 bg-gray-200">EXP</a>
                    <input name="expired" type="date" class="w-full rounded-e border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Inputkan expired obat" />
                </div>
                <button onclick="addStuff()" class="rounded-lg bg-blue-500 py-2 text-white hover:bg-blue-700 text-center">
                    Tambah
                </button>
            </div>
        </div>
    </div>
</div>
<div class="rounded-lg bg-white p-6 shadow-lg mt-4">
    <div class="mt-8">
        <div class="flex justify-between mb-4">
            <h1>Total: <span id="total" class="font-bold">Rp 0</span></h1>
            <button onclick="customBuatModal('saveDrug', 'add-stuff-form')" class="rounded-lg bg-blue-500 px-8 py-1 text-white hover:bg-blue-600 ">
                Simpan
            </button>
        </div>
        <div class="overflow-hidden rounded-lg bg-white shadow-md">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2 text-center w-1">No</th>
                        <th class="border p-2 text-center">Nama Obat</th>
                        <th class="border p-2 text-center">Jumlah</th>
                        <th class="border p-2 text-center">Harga Satuan</th>
                        <th class="border p-2 text-center">Subtotal</th>
                        <th class="border p-2 text-center">Expired</th>
                        <th class="border p-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700" id="drugTable">
                    @if(isset($importedData) && $importedData->isNotEmpty())
                        @foreach($importedData as $index => $data)
                            <tr>
                                <td class="border p-2 text-center">{{ $index + 1 }}</td>
                                <td class="border p-2 text-center">{{ $data['nama_obat'] ?? '-' }}</td>
                                <td class="border p-2 text-center">{{ $data['jumlah'] ?? '0' }}</td>
                                <td class="border p-2 text-center">{{ $data['harga_satuan'] ?? '0' }}</td>
                                <td class="border p-2 text-center">{{ ($data['jumlah'] ?? 0) * ($data['harga_satuan'] ?? 0) }}</td>
                                <td class="border p-2 text-center">{{ $data['tanggal_exp'] ?? '-' }}</td>
                                <td class="border p-2 text-center">
                                    <button type="button" onclick="showDeleteModal({{$index}})" class="bg-red-500 text-white text-sm px-2 py-2 rounded-lg shadow hover:bg-red-600">
                                        <svg width="20" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z" fill="white"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="deleteItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeDeleteModal()">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
        <div class="text-center">
            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Anda yakin untuk menghapus data ini?</h3>
            <p class="text-sm text-gray-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                Batal</button>
            <button onclick="deleteItem()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none">
                Hapus</button>
        </div>
    </div>
</div>
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeUploadModal()">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
            </svg>
            <span class="sr-only">Close modal</span>
        </button>

        <!-- Tambahkan Form -->
        <form id="import-inventory-form" action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data">
            @csrf  <!-- Token CSRF wajib untuk keamanan Laravel -->

            <div class="flex items-center justify-center w-full mb-6 mt-6">
                <label for="dropzone-file" id="dropzone-label" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div id="dropzone-content" class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                        </svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500">File format .xls (Max. 10Mb)</p>
                    </div>
                    <input id="dropzone-file" name="file" type="file" class="hidden" required />
                </label>

                <!-- Info File yang Diunggah -->
                <div id="file-info" class="hidden w-full mt-2 flex flex-col items-center bg-gray-100 p-4 rounded-lg">
                    <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                    <button type="button" id="remove-file" class="mt-2 text-red-500 text-sm hover:underline">Ganti File</button>
                </div>
            </div>


            <div class="flex justify-center space-x-4">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                    Import Excel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="destinationModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="closeDestinationModal()">
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
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih tujuan penempatan obat?</h3>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="selectDestination('warehouse')"
                class="px-4 py-2 bg-gray-200 text-white text-gray-900 rounded-lg hover:bg-blue-600 focus:outline-none">
               Inventory
            </button>
            <button onclick="selectDestination('clinic')" type="button"
                class="px-4 py-2 bg-gray-200 text-white text-gray-900 rounded-lg hover:bg-blue-600 focus:outline-none">
                Klinik
            </button>
        </div>
    </div>
</div>

<script>
    let deleteForItem = null;
    document.addEventListener('DOMContentLoaded', function() {
        const drugInput = document.querySelector("input[name='drug']")
        const unitInput = document.querySelector("select[name='unit']")
        const quantityInput = document.querySelector("input[name='quantity']")
        unitInput.addEventListener('change', function(e) {
            const name = drugInput.value
            fetch(`/get-drug-price/${name}/${unitInput.value}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector("input[name='price']").value = data.price * (
                        quantityInput.value == 0 ? 1 : quantityInput.value);
                })
                .catch(error => console.error('Error:', error));
        })
        quantityInput.addEventListener('input', function(e) {
            const name = drugInput.value
            fetch(`/get-drug-price/${name}/${unitInput.value}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector("input[name='price']").value = data.price * quantityInput
                        .value;
                })
                .catch(error => console.error('Error:', error));
        })
        let timeout = null;

        drugInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value;
            timeout = setTimeout(() => {
                if (query.length > 0) {
                    fetch(`/drug-suggestions?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            const suggestions = document.getElementById('suggestions');
                            suggestions.innerHTML = '';

                            if (data.length > 0) {
                                suggestions.classList.remove('hidden');
                                data.forEach(item => {
                                    const option = document.createElement('li');
                                    option.textContent = `${item.name}`;
                                    option.classList.add('p-2', 'cursor-pointer',
                                        'hover:bg-gray-100');
                                    option.addEventListener('click', () => {
                                        document.getElementById('drugInput')
                                            .value = item.name;
                                        suggestions.classList.add('hidden');
                                        const name = drugInput.value
                                        fetch(`/get-drug-price/${name}/pcs`)
                                            .then(response => response
                                                .json())
                                            .then(data => {
                                                document.querySelector(
                                                        "input[name='price']"
                                                    ).value = data
                                                    .price * (
                                                        quantityInput
                                                        .value == 0 ?
                                                        1 :
                                                        quantityInput
                                                        .value);
                                            })
                                            .catch(error => console.error(
                                                'Error:', error));
                                    });
                                    suggestions.appendChild(option);
                                });
                            } else {
                                suggestions.classList.add('hidden');
                            }
                        });
                } else {
                    document.getElementById('suggestions').classList.add('hidden');
                }
            }, 400);
        });

    });

    let data = []
    let total = 0;

    @if(isset($importedData) && $importedData->isNotEmpty())
        @php
            $jsonData = $importedData->map(function($data){
                return [
                    $data["nama_obat"],
                    $data["jumlah"],
                    $data["satuan"],
                    $data["harga_satuan"],
                    $data["tanggal_exp"],
                ];
            });
            $jsonData = json_encode($jsonData);
        @endphp
        data = JSON.parse(`{!! $jsonData !!}`)
    @endif

    function addStuff() {
        let drug = document.querySelector("input[name='drug']")
        let quantity = document.querySelector("input[name='quantity']")
        let unit = document.querySelector("select[name='unit']")
        let price = document.querySelector("input[name='price']")
        let expired = document.querySelector("input[name='expired']")
        let input = [drug, quantity, unit, price, expired]
        let datainput = input.map(e => e.value)
        
        if (datainput[0] === "") {
            showToastError('Silakan pilih obat terlebih dahulu');
            return;
        }
        
        if (datainput[1] === "" || parseInt(datainput[1]) <= 0) {
            showToastError('Jumlah harus lebih dari 0');
            return;
        }
        
        if (datainput[3] === "" || parseFloat(datainput[3]) <= 0) {
            showToastError('Harga harus lebih dari 0');
            return;
        }
        
        if (datainput[4] === "") {
            showToastError('Silakan pilih tanggal expired');
            return;
        }
        
        const expiredDate = new Date(datainput[4]);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (expiredDate < today) {
            showToastError('Tanggal expired tidak boleh di masa lalu');
            return;
        }
        
        const existingIndex = data.findIndex(item => 
            item[0] === datainput[0] && item[4] === datainput[4]
        );
        if (existingIndex !== -1) {
            data[existingIndex][1] = (parseInt(data[existingIndex][1]) + parseInt(datainput[1])).toString();
            data[existingIndex][3] = (parseFloat(data[existingIndex][3]) + parseFloat(datainput[3])).toString();
            showToastSuccess(`Jumlah ${datainput[0]} (exp: ${datainput[4]}) ditambahkan menjadi ${data[existingIndex][1]}`);
            draw();
            input.forEach(e => { e.value = null; });
            return;
        }
        data.push(datainput);
        showToastSuccess(`Berhasil menambahkan ${datainput[1]} ${datainput[2]} ${datainput[0]}`);
        draw();
        input.forEach(e => { e.value = null; });
    }

    function showToastError(message) {
        const toast = document.createElement('div');
        toast.id = 'temp-toast-error';
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
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
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

    function showToastSuccess(message) {
        const toast = document.createElement('div');
        toast.id = 'temp-toast-success';
        toast.className = 'fixed right-5 top-5 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow light:bg-gray-800 light:text-gray-400';
        toast.innerHTML = `
            <div class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-500 light:bg-green-800 light:text-green-200">
                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            <button type="button" onclick="this.parentElement.remove()" class="-mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 light:bg-gray-800 light:text-gray-500 light:hover:bg-gray-700 light:hover:text-white">
                <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0l6 6M7 7l6-6M7 7L1 13" />
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

    function draw() {
        total = 0;
        document.querySelector("#drugTable").innerHTML = ""
        data.forEach((e, i) => {
            document.querySelector("#drugTable").innerHTML += row(e, i)
        });
        document.querySelector("#total").innerHTML = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(total)
    }

    function row(datainput, i) {
        [drug, quantity, unit, piece_price, expired] = datainput
        total += parseFloat(piece_price) * parseInt(quantity)
        return `<tr class="border-b border-gray-200 hover:bg-gray-100 text-center">
                                <td>${i + 1}</td>
                                <td class="text-left">${drug}</td>
                                <td>${quantity} ${unit}</td>
                                <td>${piece_price}</td>
                                <td>${parseFloat(piece_price) * parseInt(quantity)}</td>
                                <td>${expired}</td>
                                <td class="py-2">
                                    <button type="button" onclick="showDeleteModal(${i})"
                                        class="bg-red-500 text-white text-sm px-2 py-2 rounded-lg shadow hover:bg-red-600">
                                        <svg width="20" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z" fill="white"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>`
    }

    function showDeleteModal(index) {
        deleteForItem = index;
        document.getElementById('deleteItem').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteItem').classList.add('hidden');
    }

    function uploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    document.getElementById('dropzone-file').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const dropzoneLabel = document.getElementById('dropzone-label');
        const dropzoneContent = document.getElementById('dropzone-content');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const removeFileBtn = document.getElementById('remove-file');

        if (file) {
            fileName.textContent = "File diunggah: " + file.name;
            dropzoneLabel.classList.add("hidden");
            fileInfo.classList.remove("hidden");
        }

        // Jika tombol "Ganti File" diklik
        removeFileBtn.addEventListener("click", function () {
            document.getElementById('dropzone-file').value = ""; // Reset input file
            dropzoneLabel.classList.remove("hidden");
            fileInfo.classList.add("hidden");
        });
    });

    function deleteItem() {
        closeDeleteModal()
        data.splice(deleteForItem, 1)
        draw()
    }

    function buatModal() {
        data = data.map(function(e) {
            return {
                name: e[0],
                quantity: parseInt(e[1]),
                unit: e[2],
                piece_price: parseFloat(e[3]),
                price: parseFloat(e[3]) * parseInt(e[1]),
                expired: e[4]
            };
        });
        document.querySelector("input[name='total']").value = total
        document.querySelector("input[name='transaction']").value = JSON.stringify(data)
        showModal('add', 'add-stuff-form')
    }

    function customBuatModal(method, form) {
        const vendorSelect = document.querySelector("select[name='vendor_id']");
        if (vendorSelect && !vendorSelect.value) {
            showToastError('Silakan pilih vendor terlebih dahulu');
            return;
        }

        const methodSelect = document.querySelector("select[name='method']");
        if (methodSelect && !methodSelect.value) {
            showToastError('Silakan pilih metode pembayaran terlebih dahulu');
            return;
        }
        if (methodSelect && methodSelect.value === 'credit') {
            const dueInput = document.querySelector("input[name='due']");
            if (dueInput && !dueInput.value) {
                showToastError('Silakan pilih tanggal jatuh tempo untuk pembayaran kredit');
                return;
            }
            const dueDate = new Date(dueInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (dueDate <= today) {
                showToastError('Tanggal jatuh tempo harus di masa depan');
                return;
            }
        }
        if (data.length === 0) {
            showToastError('Silakan tambahkan minimal satu item obat');
            return;
        }
        data = data.map(function(e) {
            return {
                name: e[0],
                quantity: parseInt(e[1]),
                unit: e[2],
                piece_price: parseFloat(e[3]),
                price: parseFloat(e[3]) * parseInt(e[1]),
                expired: e[4]
            };
        });
        document.querySelector("input[name='total']").value = total;
        document.querySelector("input[name='transaction']").value = JSON.stringify(data);
        showDestinationModal();
    }

    function showDestinationModal() {
        document.getElementById('destinationModal').classList.remove('hidden');
    }

    function closeDestinationModal() {
        document.getElementById('destinationModal').classList.add('hidden');
    }

    function selectDestination(destination) {
        document.querySelector("input[name='destination']").value = destination;
        closeDestinationModal();
        showModal('add', 'add-stuff-form');
    }

    document.getElementById('import-inventory-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const fileInput = form.querySelector('input[name="file"]');
        if (!fileInput.files.length) {
            showToastError('Silakan pilih file terlebih dahulu');
            return;
        }
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Gagal mengimpor data');
            }
            return response.json();
        })
        .then(result => {
            if (result.data && Array.isArray(result.data)) {
                result.data.forEach(row => {
                    const namaObat = row.nama_obat || row.name || '';
                    const jumlah = row.jumlah || row.quantity || '';
                    const satuan = row.satuan || row.unit || '';
                    const hargaSatuan = row.harga_satuan || row.price || '';
                    const tanggalExp = row.tanggal_exp || row.expired || '';
                    const existingIndex = data.findIndex(item => 
                        item[0] === namaObat && item[4] === tanggalExp
                    );
                    if (existingIndex !== -1) {
                        data[existingIndex][1] = (parseInt(data[existingIndex][1]) + parseInt(jumlah)).toString();
                        data[existingIndex][3] = (parseFloat(data[existingIndex][3]) + parseFloat(hargaSatuan)).toString();
                    } else {
                        data.push([namaObat, jumlah, satuan, hargaSatuan, tanggalExp]);
                    }
                });
                draw();
                closeUploadModal();
                showToastSuccess('Berhasil mengimpor data dari Excel!');
            } else {
                showToastError('Format data tidak valid.');
            }
        })
        .catch(error => {
            showToastError(error.message || 'Gagal mengimpor data');
        });
    });
</script>
@endsection