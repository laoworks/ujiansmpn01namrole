@php
    $kembali = auth()->user()->hasRole('guru')
        ? route('guru.ujian.show', $ujian)
        : route('pengawas.ujian.index');

    // Warna tombol. Tulis class secara utuh (jangan disambung).
    $tombol = 'inline-block px-4 py-2 text-white rounded-md text-sm';
    $tombolKecil = 'inline-block px-3 py-1.5 text-white rounded-md text-xs';
    $warnaBuat = 'bg-green-600 hover:bg-green-700';
    $warnaCetak = 'bg-teal-600 hover:bg-teal-700';
    $warnaReset = 'bg-rose-600 hover:bg-rose-700';
    $warnaKembali = 'bg-gray-800 hover:bg-gray-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Token Ujian</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan cadangan: disembunyikan otomatis kalau SweetAlert aktif --}}
            @if (session('status'))
                <div role="alert" data-alert-cadangan class="p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" data-alert-cadangan class="p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $ujian->judul }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ $ujian->mapel->nama }} &middot; {{ $ujian->fase }} &middot;
                            {{ $ujian->mulai_at->format('d M Y H:i') }} s/d {{ $ujian->selesai_at->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-6 text-sm text-gray-700">
                    <div>Peserta: <strong>{{ $total }}</strong></div>
                    <div>Sudah punya token: <strong>{{ $adaToken }}</strong></div>
                    <div>Sudah dipakai masuk: <strong>{{ $dipakai }}</strong></div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    @if ($ujian->status !== 'selesai' && $adaToken < $total)
                        <form method="POST" action="{{ route('token.buat', $ujian) }}">
                            @csrf
                            <button type="submit" class="{{ $tombol }} {{ $warnaBuat }}">
                                Buat token ({{ $total - $adaToken }} siswa)
                            </button>
                        </form>
                    @endif

                    @if ($adaToken > 0)
                        <a href="{{ route('token.cetak', $ujian) }}" target="_blank"
                           class="{{ $tombol }} {{ $warnaCetak }}">
                            Cetak daftar token
                        </a>
                    @endif
                </div>
            </div>

            @forelse ($perKelas as $namaKelas => $daftar)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <h4 class="font-semibold text-gray-800">Kelas {{ $namaKelas }}</h4>

                    <table class="w-full text-left text-sm mt-3">
                        <thead>
                            <tr class="border-b text-gray-600">
                                <th class="py-2">NIS</th>
                                <th class="py-2">Nama</th>
                                <th class="py-2">Token</th>
                                <th class="py-2">Status</th>
                                <th class="py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $s)
                                @php($t = $tokens->get($s->id))
                                <tr class="border-b">
                                    <td class="py-2">{{ $s->username }}</td>
                                    <td class="py-2">{{ $s->name }}</td>
                                    <td class="py-2 font-mono font-bold tracking-widest">
                                        {{ $t?->token ?? '-' }}
                                    </td>
                                    <td class="py-2">
                                        @if (! $t)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">Belum ada token</span>
                                        @elseif ($t->dipakai_at)
                                            <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 whitespace-nowrap">
                                                Sudah dipakai {{ $t->dipakai_at->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Belum dipakai</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right">
                                        @if ($t && $ujian->status !== 'selesai')
                                            {{-- Konfirmasi lewat SweetAlert --}}
                                            <form method="POST" action="{{ route('token.reset', [$ujian, $s]) }}" class="inline"
                                                  data-judul="Reset token?"
                                                  data-konfirmasi="Token {{ $s->name }} akan direset. Token lama tidak berlaku lagi."
                                                  data-tombol="Ya, reset"
                                                  data-warna="#e11d48">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="{{ $tombolKecil }} {{ $warnaReset }}">Reset</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">
                    Belum ada siswa aktif di kelas peserta ujian ini.
                </div>
            @endforelse

            <a href="{{ $kembali }}" class="{{ $tombol }} {{ $warnaKembali }}">&larr; Kembali</a>
        </div>
    </div>

    {{-- Notifikasi dan konfirmasi SweetAlert --}}
    <script>
        // Konfirmasi untuk form yang punya atribut data-konfirmasi.
        // Fase capture supaya tidak bentrok dengan handler global (kalau ada).
        // Jika SweetAlert belum dimuat, dipakai confirm() bawaan browser.
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (! form.matches || ! form.matches('form[data-konfirmasi]')) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            const kirim = function () { form.submit(); };

            if (! window.Swal) {
                if (confirm(form.dataset.konfirmasi || 'Yakin?')) kirim();
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: form.dataset.judul || 'Yakin?',
                text: form.dataset.konfirmasi || '',
                showCancelButton: true,
                confirmButtonText: form.dataset.tombol || 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: form.dataset.warna || '#2563eb',
                cancelButtonColor: '#6b7280',
            }).then(function (hasil) {
                if (hasil.isConfirmed) kirim();
            });
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            if (! window.Swal) return;

            // SweetAlert aktif: sembunyikan kotak pesan cadangan
            document.querySelectorAll('[data-alert-cadangan]').forEach((el) => el.remove());

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