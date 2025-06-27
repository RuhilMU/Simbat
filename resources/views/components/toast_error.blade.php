<div id="toast-success"
            class="fixed hidden right-5 top-5 mb-4 flex w-full max-w-xs items-center rounded-lg bg-white p-4 text-gray-500 shadow light:bg-gray-800 light:text-gray-400"
            role="alert">
            <div
                class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-500 light:bg-red-800 light:text-green-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                </svg>
                <span class="sr-only">Check icon</span>
            </div>
            <div class="ml-3 text-sm font-normal">
                @if($errors->any())
                    @php
                        $hasUniqueError = false;
                        foreach($errors->all() as $error) {
                            if (str_contains($error, 'sudah ada')) {
                                $hasUniqueError = true;
                                break;
                            }
                        }
                    @endphp
                    @if($hasUniqueError)
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    @elseif(session('error'))
                        {{ session('error') }}
                    @endif
                @elseif(session('error'))
                    {{ session('error') }}
                @endif
            </div>
            <button type="button" onclick=""
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
        </div>
        <script>
            const toast = document.getElementById('toast-success');
            toast.classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('toast-success').classList.add('hidden');
            }, 2000);
        </script>