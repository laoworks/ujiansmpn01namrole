<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hasil Ujian</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Kartu info ujian + aksi --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $ujian->judul }}</h3>
                        <p class="text-sm text-gray-500">{{ $ujian->mapel->nama }} &middot; {{ $ujian->fase }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        {{-- BARU: konfirmasi lewat SweetAlert --}}
                        <form method="POST" action="{{ route('guru.hasil.tampil', $ujian) }}" class="inline"
                              data-judul="{{ $ujian->tampil_nilai ? 'Sembunyikan nilai?' : 'Tampilkan nilai?' }}"
                              data-konfirmasi="{{ $ujian->tampil_nilai ? 'Siswa tidak akan bisa melihat nilai mereka lagi.' : 'Siswa akan bisa melihat nilai mereka sendiri.' }}"
                              data-ikon="question"
                              data-tombol="{{ $ujian->tampil_nilai ? 'Ya, sembunyikan' : 'Ya, tampilkan' }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="px-4 py-2 rounded text-white text-xs {{ $ujian->tampil_nilai ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $ujian->tampil_nilai ? 'Sembunyikan nilai dari siswa' : 'Tampilkan nilai ke siswa' }}
                            </button>
                        </form>

                        <a href="{{ route('guru.hasil.ekspor', $ujian) }}"
                           class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                            Unduh Excel (CSV)
                        </a>
                    </div>
                </div>

                <p class="mt-3 text-sm">
                    Nilai saat ini:
                    @if ($ujian->tampil_nilai)
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Terlihat oleh siswa</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">Disembunyikan dari siswa</span>
                    @endif
                </p>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center">
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold">{{ $stat['peserta'] }}</div>
                        <div class="text-xs text-gray-500">Peserta</div>
                    </div>
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold">{{ $stat['mulai'] }}</div>
                        <div class="text-xs text-gray-500">Sudah mulai</div>
                    </div>
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold">{{ $stat['selesai'] }}</div>
                        <div class="text-xs text-gray-500">Selesai</div>
                    </div>
                    <div class="p-3 rounded bg-blue-50">
                        <div class="text-xl font-bold">{{ $stat['rata'] !== null ? number_format($stat['rata'], 2) : '-' }}</div>
                        <div class="text-xs text-gray-500">Rata-rata</div>
                    </div>
                    <div class="p-3 rounded bg-green-50">
                        <div class="text-xl font-bold">{{ $stat['tertinggi'] !== null ? number_format($stat['tertinggi'], 2) : '-' }}</div>
                        <div class="text-xs text-gray-500">Tertinggi</div>
                    </div>
                    <div class="p-3 rounded bg-red-50">
                        <div class="text-xl font-bold">{{ $stat['terendah'] !== null ? number_format($stat['terendah'], 2) : '-' }}</div>
                        <div class="text-xs text-gray-500">Terendah</div>
                    </div>
                </div>
            </div>

            {{-- Satu kartu tabel per kelas --}}
            @foreach ($perKelas as $namaKelas => $daftar)
                @php
                    $rataKelas = $daftar
                        ->map(fn ($s) => $sesi->get($s->id))
                        ->filter(fn ($x) => $x && $x->status === 'selesai' && $x->nilai !== null)
                        ->avg('nilai');
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h4 class="font-semibold text-gray-800">Kelas {{ $namaKelas }}</h4>
                        <span class="text-sm text-gray-600">
                            Rata-rata kelas:
                            <strong>{{ $rataKelas !== null ? number_format($rataKelas, 2) : '-' }}</strong>
                        </span>
                    </div>

                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-100 text-gray-700">
                                <th class="py-2 px-3">NIS</th>
                                <th class="py-2 px-3">Nama</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Mulai</th>
                                <th class="py-2 px-3">Selesai</th>
                                <th class="py-2 px-3">Skor</th>
                                <th class="py-2 px-3">Nilai</th>
                                <th class="py-2 px-3">Pelanggaran</th>
                                <th class="py-2 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftar as $s)
                                @php($x = $sesi->get($s->id))

                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3">{{ $s->username }}</td>
                                    <td class="py-2 px-3">{{ $s->name }}</td>
                                    <td class="py-2 px-3">
                                        @if (! $x)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 whitespace-nowrap">Belum mulai</span>
                                        @elseif ($x->status === 'berlangsung')
                                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">Mengerjakan</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Selesai</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">{{ $x?->mulai_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2 px-3 whitespace-nowrap">{{ $x?->selesai_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        {{ $x && $x->status === 'selesai' && $x->skor !== null
                                            ? $x->skor . ' / ' . $x->skor_maks
                                            : '-' }}
                                    </td>
                                    <td class="py-2 px-3 font-bold">
                                        {{ $x && $x->status === 'selesai' && $x->nilai !== null
                                            ? number_format($x->nilai, 2)
                                            : '-' }}
                                    </td>
                                    <td class="py-2 px-3 {{ $x && $x->pelanggaran > 0 ? 'text-red-600 font-semibold' : '' }}">
                                        {{ $x ? $x->pelanggaran : '-' }}
                                    </td>
                                    <td class="py-2 px-3 text-right whitespace-nowrap">
                                        @if ($x && $x->status === 'selesai')
                                            <a href="{{ route('guru.hasil.detail', [$ujian, $x]) }}"
                                               class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                                                Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            @if ($perKelas->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">Belum ada peserta.</div>
            @endif

            <a href="{{ route('guru.ujian.show', $ujian) }}" class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">&larr; Kembali ke detail ujian</a>
        </div>
    </div>

    {{-- BARU: notifikasi SweetAlert --}}
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