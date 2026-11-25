<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->exists ? 'Edit Akun' : 'Tambah Akun' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $aksi }}" x-data="{ role: '{{ old('role', $user->role) }}' }">
                    @csrf
                    @if ($user->exists)
                        @method('PATCH')
                    @endif

                    <div>
                        <x-input-label for="name" value="Nama lengkap" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
                            :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="username" value="Username (NIS / NIP)" />
                        <x-text-input id="username" name="username" type="text" class="block mt-1 w-full"
                            :value="old('username', $user->username)" required />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" value="Role" />
                        <select id="role" name="role" x-model="role"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['admin' => 'Admin', 'guru' => 'Guru', 'pengawas' => 'Pengawas', 'siswa' => 'Siswa'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="role === 'siswa'">
                        <x-input-label for="kelas_id" value="Kelas" />
                        <select id="kelas_id" name="kelas_id"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- pilih kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" @selected((string) old('kelas_id', $user->kelas_id) === (string) $k->id)>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="role === 'siswa'">
                        <x-input-label for="no_wa" value="Nomor WhatsApp siswa / orang tua (untuk hasil ujian)" />
                        <x-text-input id="no_wa" name="no_wa" type="text" class="block mt-1 w-full"
                            :value="old('no_wa', $user->no_wa)" placeholder="081234567890" />
                        <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password"
                            :value="$user->exists ? 'Password baru (kosongkan jika tidak diubah)' : 'Password'" />
                        <x-text-input id="password" name="password" type="text" class="block mt-1 w-full"
                            :required="! $user->exists" autocomplete="off" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label class="inline-flex items-center mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                            @checked(old('is_active', $user->is_active))>
                        <span class="ml-2 text-sm text-gray-700">Akun aktif</span>
                    </label>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="text-gray-600">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>