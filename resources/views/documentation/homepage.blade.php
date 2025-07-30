
@section('action-buttons')
    {{-- Tidak ada action-buttons di halaman homepage/folder --}}
@endsection


    <div class="prose max-w-none">
        <div class="text-center p-8 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">
                @if(isset($selectedNavItem) && $selectedNavItem->menu_status == 0)
                    Ini adalah Menu Folder
                @else
                    Selamat Datang!
                @endif
            </h3>
            <p class="text-gray-600">
                @if(isset($fallbackMessage))
                    {!! $fallbackMessage !!}
                @else
                    Menu ini berfungsi sebagai pengelompokan untuk sub-menu di bawahnya dan tidak memiliki konten yang dapat diedit langsung. Silakan pilih sub-menu di sidebar.
                @endif
            </p>
            @auth
                @if(auth()->user()->role === 'admin')
                    <p class="mt-4 text-sm text-gray-500">Anda dapat mengubah status menu ini menjadi 'Memiliki Konten' melalui tombol edit menu di sidebar untuk membuat daftar tindakan (use case).</p>
                @endif
            @endauth
        </div>
    </div>
