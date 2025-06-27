<div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeModal()">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
        <div class="text-center">
            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Anda yakin untuk menghapus data ini?</h3>
            <p class="text-sm text-gray-500 mb-5">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                Batal</button>
            <button onclick="submitForm()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none">
                Hapus</button>
        </div>
    </div>
</div>
<div id="saveModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="closeModal()">
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
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Apakah Anda yakin ingin menyimpan perubahan ini?</h3>
            <p class="text-sm text-gray-500 mb-5">Pastikan semua data sudah benar sebelum menyimpan.</p>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="closeModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                Batal
            </button>
            <button onclick="submitForm()" type="button"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                Simpan
            </button>
        </div>
    </div>
</div>
<div id="addModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="closeModal()">
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
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Apakah Anda yakin ingin menambahkan data ini?</h3>
            <p class="text-sm text-gray-500 mb-5">Pastikan semua data sudah benar sebelum menyimpan.</p>
        </div>
        <div class="flex justify-center space-x-4">
            <button onclick="closeModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                Batal
            </button>
            <button onclick="submitForm()" type="button"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                Simpan
            </button>
        </div>
    </div>
</div>

<!-- <div id="saveDrugModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            onclick="closeModal()">
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
            <button onclick="submitForm()"
                class="px-4 py-2 bg-gray-200 text-white text-gray-900 rounded-lg hover:bg-blue-600 focus:outline-none">
               Inventory
            </button>
            <button onclick="customSubmitForm(@js(route('clinic.inflows.store')))" type="button"
                class="px-4 py-2 bg-gray-200 text-white text-gray-900 rounded-lg hover:bg-blue-600 focus:outline-none">
                Klinik
            </button>
        </div>
    </div>
</div> -->

<script>
    let formData;
    function showModal(method,form) {
        switch (method) {
            case "delete":
                document.getElementById('deleteModal').classList.remove('hidden')
                break;
            case "add":
                document.getElementById('addModal').classList.remove('hidden')
                break;
            case "save":
                    document.getElementById('saveModal').classList.remove('hidden')
                break;
            case "saveDrug":
                document.getElementById('saveDrugModal').classList.remove('hidden')
                break;
            default:
                break;
        }
        formData = document.getElementById(form);
        window.addEventListener('keydown',(e)=>{
            submitForm()
        })
    }
    function closeModal(){
        document.querySelectorAll('.modal').forEach(e=>e.classList.add('hidden'));
        formData = null;
    }
    function submitForm(){
        try {

            formData.removeEventListener('submit',()=>{});
        } catch (error) {

        }
        formData.submit()
    }

    function customSubmitForm(url){
        try {

            formData.removeEventListener('submit',()=>{});
        } catch (error) {

        }
        formData.action = url;
        formData.submit()
    }
</script>
