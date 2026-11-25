@php
    [$warna, $titik] = match ($ujian->fase) {
        'Draft' => ['bg-gray-100 text-gray-700', 'bg-gray-400'],
        'Berlangsung' => ['bg-green-100 text-green-700', 'bg-green-500'],
        'Menunggu jadwal' => ['bg-blue-100 text-blue-700', 'bg-blue-500'],
        default => ['bg-orange-100 text-orange-700', 'bg-orange-500'],
    };

    $daftarSesi = $ujian->jadwalEfektif();
    $sesiBerjalan = $ujian->jadwalBerjalan();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Ujian
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan sukses (cadangan: disembunyikan otomatis kalau SweetAlert aktif) --}}
            @if (session('status'))
                <div role="alert" data-alert-cadangan class="p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pesan error validasi (cadangan: disembunyikan otomatis kalau SweetAlert aktif) --}}
            @if ($errors->any())
                <div role="alert" data-alert-cadangan class="p-3 rounded bg-red-100 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Kartu info ujian --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $ujian->judul }}
                    </h3>

                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-sm whitespace-nowrap {{ $warna }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $titik }}"></span>
                        {{ $ujian->fase }}
                    </span>
                </div>

                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500 inline">Mapel:</dt>
                        <dd class="inline text-gray-800">{{ $ujian->mapel->nama }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 inline">Durasi:</dt>
                        <dd class="inline text-gray-800">{{ $ujian->durasi_menit }} menit</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 inline">Acak soal:</dt>
                        <dd class="inline text-gray-800">{{ $ujian->acak_soal ? 'Ya' : 'Tidak' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 inline">Jumlah soal:</dt>
                        <dd class="inline text-gray-800">{{ $soalList->count() }} (total {{ $totalPoin }} poin)</dd>
                    </div>
                </dl>

                <div class="mt-4 text-sm flex flex-wrap items-center gap-2">
                    <span class="text-gray-500">Kelas peserta:</span>

                    @foreach ($ujian->kelas as $k)
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                            {{ $k->nama }}
                        </span>
                    @endforeach

                    <span class="text-gray-600">({{ $peserta }} siswa aktif)</span>
                </div>

                {{-- Aksi status ujian: konfirmasi lewat SweetAlert --}}
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    @if ($ujian->status === 'draft')
                        <form
                            method="POST"
                            action="{{ route('guru.ujian.aktifkan', $ujian) }}"
                            data-judul="Aktifkan ujian?"
                            data-konfirmasi="Ujian &quot;{{ $ujian->judul }}&quot; akan diaktifkan."
                            data-tombol="Ya, aktifkan"
                            data-warna="#16a34a"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                            >
                                Aktifkan
                            </button>
                        </form>

                        <a
                            href="{{ route('guru.ujian.edit', $ujian) }}"
                            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                        >
                            Edit
                        </a>
                    @elseif ($ujian->status === 'aktif')
                        <form
                            method="POST"
                            action="{{ route('guru.ujian.tutup', $ujian) }}"
                            data-judul="Tutup ujian?"
                            data-konfirmasi="Ujian &quot;{{ $ujian->judul }}&quot; akan ditutup."
                            data-tombol="Ya, tutup"
                            data-warna="#dc2626"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700"
                            >
                                Tutup ujian
                            </button>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('guru.ujian.draft', $ujian) }}"
                            data-judul="Kembalikan ke Draft?"
                            data-konfirmasi="Ujian &quot;{{ $ujian->judul }}&quot; akan dikembalikan ke status Draft."
                            data-tombol="Ya, kembalikan"
                            data-warna="#4b5563"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700"
                            >
                                Kembalikan ke Draft
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Kartu jadwal sesi --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-semibold text-gray-800">Jadwal sesi</h4>

                    @if ($ujian->status !== 'draft')
                        <span class="px-2 py-0.5 rounded-full text-sm bg-yellow-100 text-yellow-800 whitespace-nowrap">
                            Belum ikut: {{ $belumIkut }} dari {{ $peserta }} siswa
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Siswa yang belum mengerjakan di sesi sebelumnya otomatis bisa masuk di sesi berikutnya.
                </p>

                <table class="w-full text-left text-sm mt-3">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-2 px-3">Sesi</th>
                            <th class="py-2 px-3">Boleh mulai sejak</th>
                            <th class="py-2 px-3">Batas akhir mulai</th>
                            @if ($ujian->status !== 'draft')
                                <th class="py-2 px-3">Siswa masuk</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($daftarSesi as $j)
                            @php $sedang = $ujian->status === 'aktif' && $sesiBerjalan && $sesiBerjalan->urutan === $j->urutan; @endphp
                            <tr class="border-b {{ $sedang ? 'bg-green-50' : 'hover:bg-gray-50' }}">
                                <td class="py-2 px-3 whitespace-nowrap">
                                    Sesi {{ $j->urutan }}
                                    @if ($sedang)
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Berlangsung</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $j->mulai_at->format('d M Y H:i') }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $j->selesai_at->format('d M Y H:i') }}</td>
                                @if ($ujian->status !== 'draft')
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                                            {{ $perSesi[$j->id] ?? 0 }} siswa
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu peserta: siswa masuk di sesi yang mana --}}
            @if ($ujian->status !== 'draft')
                <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <h4 class="font-semibold text-gray-800">Peserta dan sesinya</h4>

                    <table class="w-full text-left text-sm mt-3">
                        <thead>
                            <tr class="border-b bg-gray-100 text-gray-700">
                                <th class="py-2 px-3 w-10">No</th>
                                <th class="py-2 px-3">Nama</th>
                                <th class="py-2 px-3">Kelas</th>
                                <th class="py-2 px-3">Sesi</th>
                                <th class="py-2 px-3">Masuk</th>
                                <th class="py-2 px-3">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pesertaList as $p)
                                @php
                                    $ss = $sesiMap->get($p->id);
                                    [$label, $kelasLabel] = match (true) {
                                        $ss === null => ['Belum ikut', 'bg-yellow-100 text-yellow-800'],
                                        $ss->status === 'selesai' => ['Selesai', 'bg-green-100 text-green-700'],
                                        default => ['Mengerjakan', 'bg-blue-100 text-blue-700'],
                                    };
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                    <td class="py-2 px-3 text-gray-800">{{ $p->name }}</td>
                                    <td class="py-2 px-3">{{ $p->kelas?->nama ?? '-' }}</td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        @if ($ss?->jadwal)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">Sesi {{ $ss->jadwal->urutan }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">{{ $ss?->mulai_at?->format('d M H:i') ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-0.5 rounded-full whitespace-nowrap {{ $kelasLabel }}">{{ $label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 px-3 text-center text-gray-500">Belum ada peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Kartu daftar soal --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <h4 class="font-semibold text-gray-800">Daftar soal</h4>

                <table class="w-full text-left text-sm mt-3">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-2 px-3 w-10">No</th>
                            <th class="py-2 px-3">Pertanyaan</th>
                            <th class="py-2 px-3">Tipe</th>
                            <th class="py-2 px-3">Poin</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($soalList as $s)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $loop->iteration }}</td>

                                <td class="py-2 px-3">
                                    {{ \Illuminate\Support\Str::limit($s->pertanyaan, 100) }}
                                </td>

                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">
                                        {{ $tipeLabel[$s->tipe] ?? $s->tipe }}
                                    </span>
                                </td>

                                <td class="py-2 px-3">{{ $s->poin }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kartu token, absensi, dan hasil --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Token masuk ujian, absensi, dan hasil pengerjaan siswa.
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('token.index', $ujian) }}"
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                    >
                        Kelola token
                    </a>

                    {{-- Pengaturan dan pengiriman hasil lewat WhatsApp --}}
                    <a href="{{ route('guru.wa.index', $ujian) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        WhatsApp
                    </a>

                    @if ($ujian->status !== 'draft')
                        {{-- Absensi dan berita acara --}}
                        <a
                            href="{{ route('absensi.index', $ujian) }}"
                            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                        >
                            Absensi &amp; berita acara
                        </a>

                        <a
                            href="{{ route('guru.hasil.index', $ujian) }}"
                            class="inline-block px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                        >
                            Lihat hasil
                        </a>
                    @endif
                </div>
            </div>

            {{-- Kembali ke daftar ujian --}}
            <a
                href="{{ route('guru.ujian.index') }}"
                class="inline-block px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700"
            >
                &larr; Kembali ke daftar ujian
            </a>

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
                icon: 'question',
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