@extends('layouts.main')
@section('container')
    <div class="p-6 bg-white rounded-lg shadow-lg">
        @if (auth()->user()->role=="super")
                
        <div class="flex justify-between items-center mb-4">
            <button onclick="showTambahModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow transition-colors duration-200">+
                Tambah User</button>
                <form action="">
                    <input type="text" id="searchInput" placeholder="Search..."
                    class="ring-2 ring-gray-300 rounded-full px-6 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </form>
            </div>
            @endif
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full text-sm text-center">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="w-1 py-3 px-6 w-1">No</th>
                        <th class="w-32 py-3 px-6">Nama</th>
                        <th class="w-24 py-3 px-6">Role</th>
                        <th class="w-24 py-3 px-6">Email</th>
                        <th class="w-48 py-3 px-6">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700" id="userTableBody">
                    @foreach ($users as $item)
                    @php
                        $av = Storage::url($item->avatar);
                    @endphp
                    @if (auth()->user()->role=='super')
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td class="py-3 px-6 text-left">{{ $item->name }}</td>
                        <td class="py-3 px-6">{{ $item->role }}</td>
                        <td class="py-3 px-6 text-left">{{ $item->email }}</td>
                        <form id="delete-form-{{ $item->id }}"
                            action="{{ route('user.destroy', $item->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                        </form>
                        <td class="flex justify-center gap-2 py-3">
                            <a onclick="showEditModal('{{ $item->id }}','{{ $av }}','{{ $item->name }}','{{ $item->role }}','{{ $item->email }}')" class="flex cursor-pointer items-center bg-yellow-300 text-white text-sm px-2 py-2 rounded-lg shadow hover:bg-yellow-400 transition-colors duration-200 mr-2">
                                <svg width="20" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.728 9.68602L14.314 8.27202L5 17.586V19H6.414L15.728 9.68602ZM17.142 8.27202L18.556 6.85802L17.142 5.44402L15.728 6.85802L17.142 8.27202ZM7.242 21H3V16.757L16.435 3.32202C16.6225 3.13455 16.8768 3.02924 17.142 3.02924C17.4072 3.02924 17.6615 3.13455 17.849 3.32202L20.678 6.15102C20.8655 6.33855 20.9708 6.59286 20.9708 6.85802C20.9708 7.12319 20.8655 7.37749 20.678 7.56502L7.243 21H7.242Z" fill="white"/>
                                </svg>
                            </a>
                            <button type="button" onclick="showModal('delete','delete-form-{{ $item->id }}')" type="button" class="bg-red-500 p-2 rounded-lg shadow hover:bg-red-600 transition-colors duration-200">
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                    d="M14.167 5.50002H18.3337V7.16669H16.667V18C16.667 18.221 16.5792 18.433 16.4229 18.5893C16.2666 18.7456 16.0547 18.8334 15.8337 18.8334H4.16699C3.94598 18.8334 3.73402 18.7456 3.57774 18.5893C3.42146 18.433 3.33366 18.221 3.33366 18V7.16669H1.66699V5.50002H5.83366V3.00002C5.83366 2.77901 5.92146 2.56704 6.07774 2.41076C6.23402 2.25448 6.44598 2.16669 6.66699 2.16669H13.3337C13.5547 2.16669 13.7666 2.25448 13.9229 2.41076C14.0792 2.56704 14.167 2.77901 14.167 3.00002V5.50002ZM15.0003 7.16669H5.00033V17.1667H15.0003V7.16669ZM7.50033 3.83335V5.50002H12.5003V3.83335H7.50033Z"
                                    fill="white" />
                                </svg>
                            </button>
                        </td>
                    </tr>                        
                    @elseif($item->email==auth()->user()->email)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6">1</td>
                        <td class="py-3 px-6 text-left">{{ $item->name }}</td>
                        <td class="py-3 px-6">{{ $item->role }}</td>
                        <td class="py-3 px-6 text-left">{{ $item->email }}</td>
                        <form id="delete-form-{{ $item->id }}"
                            action="{{ route('user.destroy', $item->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                        </form>
                        <td class="flex justify-center gap-2 py-3">
                            <a onclick="showEditModal('{{ $item->id }}','{{ $av }}','{{ $item->name }}','{{ $item->role }}','{{ $item->email }}')" class="flex cursor-pointer items-center bg-yellow-300 text-white text-sm px-2 py-2 rounded-lg shadow hover:bg-yellow-400 transition-colors duration-200 mr-2">
                                <svg width="20" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.728 9.68602L14.314 8.27202L5 17.586V19H6.414L15.728 9.68602ZM17.142 8.27202L18.556 6.85802L17.142 5.44402L15.728 6.85802L17.142 8.27202ZM7.242 21H3V16.757L16.435 3.32202C16.6225 3.13455 16.8768 3.02924 17.142 3.02924C17.4072 3.02924 17.6615 3.13455 17.849 3.32202L20.678 6.15102C20.8655 6.33855 20.9708 6.59286 20.9708 6.85802C20.9708 7.12319 20.8655 7.37749 20.678 7.56502L7.243 21H7.242Z" fill="white"/>
                                </svg>
                            </a>
                        </td>
                    </tr>                        
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-6">
            {{ $users->links() }}
        </div>
    </div>
    <div id="tambahModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <form method="POST" id="create-user-form" enctype="multipart/form-data">
                    @csrf
                    <input id="avatar-file" name="avatar" type="file" class="hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
                        <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeTambahModal()">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    <div class="text-center">
                        <h2 class="text-center text-xl font-semibold mb-6">Tambah Akun</h2>
                            <div class="flex items-center mb-4">
                                <div class="w-28 h-28 rounded-full bg-gray-700 overflow-hidden">
                                    <img id="avatar-image" src="{{ asset('storage/avatar/Avatar.jpg') }}" alt="Avatar" class="object-cover w-full h-full">
                                </div>
                                <div class="ml-4">
                                    <div onclick="uploadModal('add')" class="bg-blue-600 text-sm text-white px-4 py-2 rounded-lg hover:bg-blue-500">
                                        Pilih gambar
                                    </div>
                                    <p class="text-gray-400 text-xs mt-1">JPG, GIF or PNG. 1MB max.</p>
                                </div>
                            </div>
                            <div class="relative mb-4">
                                <label class="mb-2 block text-md font-medium text-left">Nama</label>
                                <input name="name" type="text" class="bg-white w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nama"/>
                            </div>
                            <div class="relative mb-4">
                                <label class="mb-2 block text-md font-medium text-left">Role</label>
                                <select name="role" class="bg-white w-full rounded border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option selected disabled te>Masukkan Role</option>
                                    <option value="super">Super Admin</option>
                                    <option value="clinic">SimKlinik</option>
                                    <option value="doctor">Dokter</option>
                                    <option value="admin">Apoteker</option>
                                </select>
                            </div>
                            <div class="mb-6">
                                <label class="mb-2 block text-md font-medium text-left">Email</label>
                                <input name="email" type="text" class="bg-white w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan email"/>
                            </div>
                            <div class="mb-6">
                                <label class="mb-2 block text-md font-medium text-left">Kata sandi</label>
                                <input name="password" type="password" class="bg-white w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan kata sandi"/>
                            </div>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <button onclick="closeAddUserModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <div id="edit-user-form" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <form method="POST" id="save-user-form" enctype="multipart/form-data">
            @csrf
                @method('PUT')
                <input id="edit-avatar-file" name="avatar" type="file" class="hidden">
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
                    <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeEditModal()">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                <div class="text-center">
                    <h2 class="text-center text-xl font-semibold mb-6">Ubah Akun</h2>
                    <div class="flex items-center mb-4">
                        <div class="w-28 h-28 rounded-full bg-gray-700 overflow-hidden">
                                <img id="avatar" src="https://via.placeholder.com/64" alt="Avatar" class="object-cover w-full h-full">
                            </div>
                            <div class="ml-4">
                                <div onclick="uploadModal('edit')" class="bg-blue-600 text-sm text-white px-4 py-2 rounded-lg hover:bg-blue-500">
                                    Pilih gambar
                                </div>
                                <p class="text-gray-400 text-xs mt-1">JPG, GIF or PNG. 1MB max.</p>
                            </div>
                        </div>
                        <div class="relative mb-4">
                            <label class="mb-2 block text-md font-medium text-left">Nama</label>
                            <input type="text" class="w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" name="name" placeholder="Masukkan nama"/>
                        </div>
                        @if (auth()->user()->role=='super')
                        <div class="relative mb-4">
                            <label class="mb-2 block text-md font-medium text-left">Role</label>
                            <select name="role" class="w-full rounded border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option selected disabled>Masukkan Role</option>
                                <option value="super">Super Admin</option>
                                <option value="clinic">SimKlinik</option>
                                <option value="doctor">Dokter</option>
                                <option value="admin">Apoteker</option>
                            </select>
                        </div>
                            
                        @endif
                        <div class="mb-6">
                            <label class="mb-2 block text-md font-medium text-left">Email</label>
                            <input name="email" type="text" class="w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan email"/>
                        </div>
                </div>
                <div class="flex justify-center space-x-4">
                    <button onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
            <input type="hidden" name="method" value="add">
            <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="closeUploadModal()">
                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="flex items-center justify-center w-full mb-6 mt-6">
                <label for="avatar-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 gray:hover:bg-gray-800 gray:bg-gray-700 hover:bg-gray-100 gray:border-gray-600 gray:hover:border-gray-500 gray:hover:bg-gray-600">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">File format .xls (Max. 10Mb)</p>
                    </div>
                </label>
            </div> 
            <div class="flex justify-center space-x-4">
                <button onclick="closeUploadModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                    Batal
                </button>
                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                    Tambah
                </button>
            </div>
        </div>
    </div> 
    <script>
        document.getElementById('create-user-form').addEventListener('submit',function(e){
            e.preventDefault()
            showModal('add','create-user-form')
            closeTambahModal()
        })
        document.getElementById('avatar-file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-image').src = e.target.result
                    // if(document.querySelector('#uploadModal input[name="method"]').value=='add'){
                    // }else{
                    //     document.querySelector('#edit-user-form img').src = e.target.result

                    // }
                };
                reader.readAsDataURL(file);
            }
            closeUploadModal()
        })
        document.getElementById('edit-avatar-file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // if(document.querySelector('#uploadModal input[name="method"]').value=='add'){
                    //     document.getElementById('avatar-image').src = e.target.result
                    // }else{
                        
                    // }
                    document.querySelector('#edit-user-form img').src = e.target.result
                };
                reader.readAsDataURL(file);
            }
            closeUploadModal()
        })
        function showEditModal(id,image, name, role, email) {
            document.querySelector('#edit-user-form input[name="name"]').value = name;
            document.querySelector('#edit-user-form input[name="email"]').value = email;
            document.querySelector('#edit-user-form img').src = image;
            document.querySelector('#edit-user-form form').setAttribute('action', `user/${id}`);
            document.querySelectorAll('#edit-user-form select[name="role"] option').forEach(e => {
                if (e.value==role) {
                    e.setAttribute('selected',true)
                }
            });
            document.getElementById('edit-user-form').classList.remove('hidden');
        }
        function showTambahModal() {
            document.getElementById('tambahModal').classList.remove('hidden');
        }
    
        function closeEditModal() {
            document.getElementById('edit-user-form').classList.add('hidden');
        }
    
        function closeTambahModal() {
            document.getElementById('tambahModal').classList.add('hidden');
        }
    
        function addUserModal() {
            document.getElementById('saveModal').classList.remove('hidden');
            document.getElementById('tambahModal').classList.add('hidden');
        }
        
        function closeAddUserModal() {
            document.getElementById('saveModal').classList.add('hidden');
        }
        document.getElementById('save-user-form').addEventListener('submit', function(e) {
            e.preventDefault()
            document.getElementById('edit-user-form').classList.add('hidden');
            showModal('save', 'save-user-form')
        })
        function uploadModal(method) {
            document.getElementById('uploadModal').classList.remove('hidden');
            document.querySelector('#uploadModal input[name="method"]').value = method;
            if(method=='edit'){
                document.querySelector('#uploadModal label').setAttribute('for','edit-avatar-file')
            }else{
                document.querySelector('#uploadModal label').setAttribute('for','avatar-file')
            }
            console.log(method);
        }
    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }
    
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableBody = document.getElementById('userTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const name = rows[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() || '';
            const role = rows[i].getElementsByTagName('td')[2]?.textContent.toLowerCase() || '';
            const email = rows[i].getElementsByTagName('td')[3]?.textContent.toLowerCase() || '';

            if (name.includes(searchValue) || role.includes(searchValue) || email.includes(searchValue)) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
    
    </script>
@endsection

