@extends('layouts.main')
@section('container')


    <form method="POST" class="hidden" action="{{ route('transaction.store') }}" id="add-stuff-form">
        @csrf
        <input type="hidden" name="transaction">
        <input type="hidden" name="source" id="sourceInput" value="warehouse">
    </form>
    <body style="margin: 40px; font-family: sans-serif;">
        <div class="flex justify-center mb-6">
            <div class="flex w-full rounded-xl overflow-hidden shadow">
                <div id="inventoryTab"
                    onclick="setActiveTab('inventory')"
                    class="flex-1 text-center py-3 bg-indigo-400 font-bold  text-white cursor-pointer">
                    Inventory
                </div>
                <div id="klinikTab"
                    onclick="setActiveTab('klinik')"
                    class="flex-1 text-center py-3 bg-indigo-400 font-bold bg-white text-gray-800 cursor-pointer">
                    Klinik
                </div>
            </div>
        </div>

    <input type="hidden" name="repackQuantity">
    <div class="fixed bottom-8 right-8 flex bg-white shadow-xl p-4 rounded-lg items-end">
        <div class="grid grid-cols-2 gap-2">

            <h1 class="text-end">Jumlah pembelian: </h1><span id="total" class="font-bold">Rp 0</span>
            <h1 class="text-end">Discount: </h1><span id="totalDisc" class="font-bold">Rp 0</span>
            <h1 class="text-end">Total dibayar: </h1><span id="totalPay" class="font-bold">Rp 0</span>

        </div>
        <button onclick="buatModal()"
            class="h-max inline-block px-6 rounded-xl bg-indigo-400 hover:bg-indigo-600 py-2 text-center font-bold text-white hover:bg-lavender-700">
            Checkout
        </button>
    </div>
    <div class="flex gap-6">
        <div class="rounded-lg p-6 shadow-lg">
            <div class="flex gap-6">
                <div>
                    <h1>Discount Transaksi</h1>
                    <div class="flex gap-4 items-center mt-3">
                        <div class="flex flex-col w-8">
                            <div class="flex justify-between">
                                <label class="text-xs font-normal" for="">Rp</label>
                                <input type="radio" name="discount" value="rupiah" id="">

                            </div>
                            <div class="flex justify-between">
                                <label class="text-xs font-normal" for="">%</label>
                                <input checked type="radio" name="discount" value="persen" id="">

                            </div>
                        </div>
                        <input type="number" value="0" name="transactionDiscount" placeholder="Discount"
                            class="w-24 rounded-md px-3 py-2 border border-gray-300" />

                    </div>
                </div>
                <div>
                    <h1>Discount Obat</h1>
                    <div class="flex gap-2 items-center mt-3">
                        <div class="flex">
                            <input value="0" type="number" name="drugDiscount"
                                class="rounded-none rounded-s-lg bg-gray-100 border cursor-not-allowed border-gray-300 text-gray-500 block w-20 text-sm p-2.5"
                                disabled>
                            <span
                                class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-e-md">
                                %
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="rounded-lg p-6 shadow-lg flex flex-col justify-between w-full">
            <div class="flex gap-3">
                <div class="flex">
                    <span
                        class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                        Stok
                    </span>
                    <input value="0" type="number" name="stock"
                        class="rounded-none rounded-e-md bg-gray-100 border cursor-not-allowed border-gray-300 text-gray-500 block w-24 text-sm px-3 py-1"
                        disabled>
                </div>
                <div class="flex">
                    <span
                        class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                        Harga
                    </span>
                    <input value="0" type="number" name="price"
                        class="rounded-none rounded-e-md bg-gray-100 border cursor-not-allowed border-gray-300 text-gray-500 block w-24 text-sm px-2 py-1"
                        disabled>
                </div>
                <input type="text" autocomplete="off" name="drug" id="drugInput" placeholder="Nama Item"
                    class="rounded-md px-3 py-1 w-full ring-1 ring-gray" />
                <ul id="suggestions"
                    class="hidden absolute top-36 right-96 mr-24 px-1 border border-gray-300 bg-white rounded">

                </ul>
                <input type="text" name="quantity" placeholder="Jumlah"
                    class="w-20 rounded-md px-3 py-1 ring-1 ring-gray" />


            </div>
            <button onclick="addStuff()"
                class="w-full rounded-md bg-indigo-400 hover:bg-indigo-600 py-1 font-bold text-white">Tambah</button>
        </div>

    </div>
    <div class="rounded-lg p-6 shadow-lg mt-6">
        <div class="w-full">

            <table class="w-full overflow-hidden rounded-lg border border-gray-300 shadow-md text-sm text-center">
                <thead class="bg-gray-200">
                    <th class="py-4 w-2 px-4">No</th>
                    <th class="py-4 w-1/2 px-2">Nama Obat</th>
                    <th class="py-4 w-max">Jumlah</th>
                    <th class="py-4">Harga Satuan</th>
                    <th class="py-4">Discount</th>
                    <th class="py-4">Total</th>
                    <th class="py-4">Aksi</th>
                </thead>
                <tbody id="drugTable">

                </tbody>
            </table>

        </div>
    </div>

    <script>
        let repackSelected = null;
        let data = [];
        let total = 0;
        let totalDisc = 0;
        let totalPay = 0;
        let discMeth = 'persen';
        let currentSource = 'warehouse';

        document.querySelectorAll("input[type='radio']").forEach(function(e) {
            e.addEventListener('change', (f) => {
                discMeth = f.target.value
                draw()
            })
        })
        document.querySelector("input[name='transactionDiscount']").addEventListener('input', () => {
            let discountInput = document.querySelector("input[name='transactionDiscount']");
            let discountValue = parseInt(discountInput.value) || 0;
            let discountType = document.querySelector('input[name="discount"]:checked').value;
            
            // Prevent negative values
            if (discountValue < 0) {
                discountInput.value = 0;
                showToastError('Diskon tidak boleh negatif');
                return;
            }
            
            // Prevent percentage over 100
            if (discountType === 'persen' && discountValue > 100) {
                discountInput.value = 100;
                showToastError('Diskon persentase tidak boleh lebih dari 100%');
                return;
            }
            
            draw();
        })

        document.addEventListener('DOMContentLoaded', function() {
            const suggestions = document.getElementById('suggestions');
            let timeout = null;
            document.querySelector("#drugInput").addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value;
                timeout = setTimeout(() => {
                    if (query.length > 0) {
                        fetch(`/drug-repack?query=${query}&source=${currentSource}`)
                            .then(response => response.json())
                            .then(data => {
                                suggestions.innerHTML = '';
                                if (data.length > 0) {
                                    suggestions.classList.remove('hidden');
                                    data.forEach(item => {
                                        const option = document.createElement('li');
                                        option.textContent = `${item.name} (${formatRupiah(item.price)})`;
                                        option.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-100');
                                        option.addEventListener('click', () => clickedOption(item));
                                        suggestions.appendChild(option);
                                    });
                                } else {
                                    suggestions.classList.add('hidden');
                                }
                            });
                    } else {
                        suggestions.classList.add('hidden');
                    }
                }, 400);
            });

        });

        function clickedOption(item) {
            const drugInput = document.querySelector("#drugInput");
            const drugStock = document.querySelector("input[name='stock']");
            const drugPrice = document.querySelector("input[name='price']");
            const repackQuantity = document.querySelector("input[name='repackQuantity']");
            const drugDiscount = document.querySelector("input[name='drugDiscount']");
            
            drugInput.value = item.name;
            suggestions.classList.add('hidden');
            repackSelected = item.id;
            drugStock.value = item.stock;
            drugPrice.value = item.price;
            repackQuantity.value = item.quantity;
            drugDiscount.value = item.drug.last_discount;
        }

        function addStuff() {
            let discTrans = document.querySelector("input[name='transactionDiscount']")
            let discDrug = document.querySelector("input[name='drugDiscount']")
            let piecePrice = document.querySelector("input[name='price']")
            let repackName = document.querySelector("input[name='drug']")
            let repackId = repackSelected
            let quantity = document.querySelector("input[name='quantity']")
            let repackQuantity = document.querySelector("input[name='repackQuantity']")
            
            // Validation: Drug must be selected
            if (!repackSelected || !repackName.value.trim()) {
                showToastError('Silakan pilih obat terlebih dahulu');
                return;
            }
            
            // Validation: Quantity must be filled and greater than 0
            if (!quantity.value || parseInt(quantity.value) <= 0) {
                showToastError('Jumlah harus lebih dari 0');
                return;
            }
            
            // Validation: Quantity must not exceed available stock
            if (parseInt(document.querySelector("input[name='stock']").value) < parseInt(quantity.value)) {
                showToastError('Stok tidak mencukupi');
                return;
            }
            
            // Validation: Transaction discount validation
            let transactionDiscount = parseInt(document.querySelector("input[name='transactionDiscount']").value) || 0;
            if (transactionDiscount < 0) {
                showToastError('Diskon transaksi tidak boleh negatif');
                return;
            }
            
            // Check if discount type is percentage and validate
            let discountType = document.querySelector('input[name="discount"]:checked').value;
            if (discountType === 'persen' && transactionDiscount > 100) {
                showToastError('Diskon persentase tidak boleh lebih dari 100%');
                return;
            }
            
            // Check for duplicate drug and update quantity if exists
            let existingIndex = data.findIndex(item => item[6] === repackId); // repackId is at index 6
            if (existingIndex !== -1) {
                // Drug already exists, update quantity
                let newQuantity = parseInt(data[existingIndex][3]) + parseInt(quantity.value); // quantity is at index 3
                let availableStock = parseInt(document.querySelector("input[name='stock']").value);
                
                if (newQuantity > availableStock) {
                    showToastError('Total stok tidak mencukupi untuk menambah jumlah ini');
                    return;
                }
                
                // Update existing item
                data[existingIndex][3] = newQuantity; // Update quantity
                data[existingIndex][5] = calculateDiscount(newQuantity * data[existingIndex][1], data[existingIndex][0]); // Update discount
                
                // Clear input fields
                let input = [discDrug, piecePrice, repackName, quantity, repackQuantity]
                input.forEach(e => {
                    e.value = null
                });
                
                draw();
                return;
            }
            
            // Add new item
            let input = [discDrug, piecePrice, repackName, quantity, repackQuantity]
            let datainput = input.map(e => e.value)
            datainput.push(calculateDiscount(quantity.value * piecePrice.value, discDrug.value))
            datainput.push(repackId)
            data.push(datainput)
            draw()
            input.forEach(e => {
                e.value = null
            });
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        } // Output: "Rp 100.000"

        function row(datainput, i) {
            [discDrug, piecePrice, repackName, quantity, repackQuantity, priceDiscount, repackId] = datainput
            total += parseInt(piecePrice * quantity)
            totalDisc += parseInt(priceDiscount)
            return `<tr class="border-b">
                        <td class="py-4">${i+1}</td>
                        <td class="py-4 px-2 text-left">${repackName} (disc ${discDrug}%)</td>
                        <td class="py-4 w-max">${quantity}</td>
                        <td class="py-4">${formatRupiah(piecePrice)}</td>
                        <td class="py-4">${formatRupiah(priceDiscount)}</td>
                        <td class="py-4">${formatRupiah(piecePrice*quantity)}</td>
                        <td class="py-3">
                            <div class="flex items-center justify-center">
                                <button onclick="deleteItem(${i})" class="rounded-xl bg-red-500 p-2 text-white">
                                    <svg width="20" height="20" viewBox="0 0 20 21" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                        d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z"
                                            fill="white" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>`
        }

        function draw() {
            total = 0;
            totalDisc = 0;
            totalPay = 0;
            document.querySelector("#drugTable").innerHTML = ""
            data.forEach((e, i) => {
                document.querySelector("#drugTable").innerHTML += row(e, i)
            });
            totalPay = total - totalDisc
            transactionDiscount()
            document.querySelector("#total").innerHTML = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(total)
            document.querySelector("#totalDisc").innerHTML = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(totalDisc)
            document.querySelector("#totalPay").innerHTML = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(totalPay)
        }

        function calculateDiscount(price, discount) {
            return price * (discount / 100);
        }

        function transactionDiscount() {
            if (discMeth == 'rupiah') {
                totalDisc += parseInt(document.querySelector("input[name='transactionDiscount']").value)
                totalPay -= parseInt(document.querySelector("input[name='transactionDiscount']").value)
            } else {
                totalDisc += calculateDiscount(totalPay, parseInt(document.querySelector(
                    "input[name='transactionDiscount']").value))
                totalPay -= calculateDiscount(totalPay, parseInt(document.querySelector("input[name='transactionDiscount']")
                    .value))
            }
        }

        function deleteItem(i) {
            // closeDeleteModal()
            data.splice(i, 1)
            draw()
        }

        function buatModal() {
            // Validation: Check if there are items in cart
            if (data.length === 0) {
                showToastError('Silakan tambahkan minimal satu item obat');
                return;
            }
            
            console.log(data);
            data = data.map(function(e) {
                return {
                    discDrug: parseInt(e[0]),
                    piecePrice: parseInt(e[1]),
                    repackName: e[2],
                    quantity: parseInt(e[3]),
                    repackQuantity: parseInt(e[4]),
                    priceDiscount: parseInt(e[5]),
                    repackId: parseInt(e[6])
                };
            });
            const sendData = {
                data,
                total,
                totalDisc,
                totalPay,
                source: currentSource
            }
            document.querySelector("input[name='transaction']").value = JSON.stringify(sendData)
            showModal('add', 'add-stuff-form')
        }


        function setActiveTab(tab) {
            const inventory = document.getElementById('inventoryTab');
            const klinik = document.getElementById('klinikTab');
            const sourceInput = document.getElementById('sourceInput');

            if (tab === 'inventory') {
              inventory.classList.add('text-white');
              inventory.classList.remove('bg-white', 'text-gray-800');

              klinik.classList.add('bg-white', 'text-gray-800');
              klinik.classList.remove('text-white');
              currentSource = 'warehouse';
            } else {
              klinik.classList.add('text-white');
              klinik.classList.remove('bg-white', 'text-gray-800');

              inventory.classList.add('bg-white', 'text-gray-800');
              inventory.classList.remove('text-white');
              currentSource = 'clinic';
            }
            sourceInput.value = currentSource;
            document.getElementById('drugInput').value = '';
            document.querySelector("input[name='stock']").value = '0';
            document.querySelector("input[name='price']").value = '0';
            document.querySelector("input[name='repackQuantity']").value = '';
            document.querySelector("input[name='drugDiscount']").value = '0';
            repackSelected = null;
            suggestions.classList.add('hidden');
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
    @if($errors->any() || session('error'))
    <div id="checkout-toast-error"
            class="fixed right-5 top-5 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow light:bg-gray-800 light:text-gray-400"
            role="alert">
            <div
                class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-500 light:bg-red-800 light:text-green-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                </svg>
                <span class="sr-only">Error icon</span>
            </div>
            <div class="ml-3 text-sm font-normal">
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                @elseif(session('error'))
                    {{ session('error') }}
                @endif
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="-mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 light:bg-gray-800 light:text-gray-500 light:hover:bg-gray-700 light:hover:text-white"
                aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('checkout-toast-error');
                if (toast) {
                    toast.remove();
                }
            }, 3000);
        </script>
    @endif
    @if(session('success'))
    <div id="checkout-toast-success"
            class="fixed right-5 top-5 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow light:bg-gray-800 light:text-gray-400"
            role="alert">
            <div
                class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-500 light:bg-green-800 light:text-green-200">
                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
                <span class="sr-only">Success icon</span>
            </div>
            <div class="ml-3 text-sm font-normal">
                {{ session('success') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="-mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 focus:ring-2 focus:ring-gray-300 light:bg-gray-800 light:text-gray-500 light:hover:bg-gray-700 light:hover:text-white"
                aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('checkout-toast-success');
                if (toast) {
                    toast.remove();
                }
            }, 3000);
        </script>
    @endif
@endsection
