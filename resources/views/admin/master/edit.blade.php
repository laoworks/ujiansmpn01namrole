<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit {{ $judul }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route($rute.'.update', $item) }}">
                    @csrf
                    @method('PATCH')

                    <x-input-label for="nama" value="Nama" />
                    <x-text-input id="nama" name="nama" type="text" class="block mt-1 w-full"
                        :value="old('nama', $item->nama)" required autofocus />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route($rute.'.index') }}" class="text-gray-600">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>