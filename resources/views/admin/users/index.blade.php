<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Akun</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('password_baru'))
                <div class="p-3 rounded bg-yellow-100 text-yellow-900">
                    Password baru untuk <strong>{{ session('password_baru')['username'] }}</strong>:
                    <code class="px-2 py-0.5 bg-white rounded font-bold">{{ session('password_baru')['password'] }}</code>
                    <span class="text-sm">(catat sekarang, tidak akan ditampilkan lagi)</span>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <form method="GET" class="flex flex-wrap gap-2">
                        <select name="role" class="border-gray-300 rounded-md shadow-sm">
                            <option value="">Semua role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        <x-text-input name="q" type="text" placeholder="Cari nama / username" :value="request('q')" />
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-blue-700">
                            Cari
                        </button>
                    </form>

                    <a href="{{ route('admin.users.create') }}"
                       class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                        + Tambah Akun
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-3 px-4">Nama</th>
                            <th class="py-2 px-3">Username</th>
                            <th class="py-2 px-3">Role</th>
                            <th class="py-2 px-3">Kelas</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $u->name }}</td>
                                <td class="py-2 px-3">{{ $u->username }}</td>
                                <td class="py-2 px-3">{{ ucfirst($u->role) }}</td>
                                <td class="py-2 px-3">{{ $u->kelas?->nama ?? '-' }}</td>
                                <td class="py-2 px-3">
                                    @if ($u->is_active)
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.users.edit', $u) }}"
                                       class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700 mr-1">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-4 py-2 rounded text-white text-xs mr-1 {{ $u->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- BARU: konfirmasi lewat SweetAlert (data-konfirmasi) --}}
                                    <form method="POST" action="{{ route('admin.users.reset', $u) }}" class="inline"
                                          data-judul="Reset password?"
                                          data-konfirmasi="Password akun {{ $u->name }} akan diganti dengan password baru."
                                          data-tombol="Ya, reset">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-4 py-2 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                                            Reset PW
                                        </button>
                                    </form>

                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline"
                                              data-judul="Hapus akun?"
                                              data-konfirmasi="Akun {{ $u->name }} akan dihapus permanen dan tidak bisa dikembalikan."
                                              data-tombol="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-4 py-2 ml-1 rounded bg-red-800 text-white text-xs hover:bg-red-900">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 px-3 text-gray-500">Tidak ada akun ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $users->links() }}</div>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">&larr; Kembali ke dashboard</a>
        </div>
    </div>

    {{-- BARU: notifikasi sukses (toast) dan error (dialog) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @js(session('status')),
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak berhasil',
                    text: @js($errors->first()),
                    confirmButtonColor: '#2563eb',
                });
            @endif
        });
    </script>
</x-app-layout>