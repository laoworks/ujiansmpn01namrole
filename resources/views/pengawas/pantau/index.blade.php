@php
    // Warna tombol. Tulis class secara utuh (jangan disambung).
    $tombol = 'inline-block px-3 py-1.5 text-white rounded-md text-xs';
    $tombolHp = 'flex items-center justify-center px-3 py-2.5 text-white rounded-md text-sm text-center';
    $warnaToken = 'bg-blue-600 hover:bg-blue-700';
    $warnaLog = 'bg-amber-500 hover:bg-amber-600';
    $warnaAkhiri = 'bg-rose-600 hover:bg-rose-700';
    $warnaKembali = 'bg-gray-800 hover:bg-gray-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Pantau Ujian</h2>
    </x-slot>

    <script>
        function pantau(init, url) {
            return {
                d: init,
                now: Date.now(),
                diambil: Date.now(),
                galat: '',
                nonaktif: false,

                mulai() {
                    setInterval(() => { this.now = Date.now(); }, 1000);
                    setInterval(() => this.ambil(), 5000);
                },

                async ambil() {
                    try {
                        const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (r.status === 403 || r.status === 404) { this.nonaktif = true; return; }
                        if (!r.ok) throw new Error('gagal');
                        this.d = await r.json();
                        this.diambil = Date.now();
                        this.galat = '';
                    } catch (e) {
                        this.galat = 'Gagal memperbarui data. Mencoba lagi...';
                    }
                },

                lewat() { return Math.floor((this.now - this.diambil) / 1000); },
                sisa(r) { return Math.max(0, r.sisa - this.lewat()); },
                diam(r) { return r.diam + this.lewat(); },

                waktu(det) {
                    const m = Math.floor(det / 60);
                    const s = det % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
                lamaDiam(r) {
                    const s = this.diam(r);
                    return s < 60 ? s + ' dtk' : Math.floor(s / 60) + ' mnt';
                },
                csrf() { return document.querySelector('meta[name=csrf-token]').content; },
            };
        }
    </script>

    {{-- Pembungkus: tidak boleh melebihi lebar layar, teks panjang dibungkus --}}
    <div class="py-4 sm:py-12 overflow-x-hidden" x-data="pantau(@js($awal), @js(route('pengawas.pantau.data', $ujian)))" x-init="mulai()">
        <div class="w-full max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-6 break-words">

            <div x-show="nonaktif" x-cloak class="p-3 rounded bg-orange-100 text-orange-800 text-sm">
                Ujian ini sudah tidak aktif, jadi pemantauan dihentikan.
                <a href="{{ route('pengawas.ujian.index') }}" class="underline">Kembali ke daftar ujian</a>
            </div>
            <div x-show="galat" x-cloak x-text="galat" class="p-3 rounded bg-red-100 text-red-800 text-sm"></div>

            {{-- Sapaan --}}
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <p class="text-gray-800">
                    Halo, <strong>{{ auth()->user()->name }}</strong>
                    <span class="inline-block ml-1 sm:ml-2 px-2 py-0.5 text-xs sm:text-sm rounded-full bg-blue-100 text-blue-700">
                        {{ auth()->user()->role }}
                    </span>
                </p>
            </div>

            {{-- Info ujian + ringkasan --}}
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">{{ $ujian->judul }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ $ujian->mapel->nama }} &middot; {{ $ujian->durasi_menit }} menit<br class="sm:hidden">
                            <span class="hidden sm:inline">&middot;</span>
                            Diperbarui <span x-text="d.waktu"></span>
                        </p>
                    </div>
                    <a href="{{ route('token.index', $ujian) }}"
                       class="flex sm:inline-flex items-center justify-center shrink-0 px-4 py-2.5 sm:py-2 {{ $warnaToken }} text-white rounded-md text-sm">
                        Kelola token
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-center">
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold" x-text="d.ringkasan.peserta"></div>
                        <div class="text-xs text-gray-500">Peserta</div>
                    </div>
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold" x-text="d.ringkasan.belum"></div>
                        <div class="text-xs text-gray-500">Belum masuk</div>
                    </div>
                    <div class="p-3 rounded bg-blue-50">
                        <div class="text-xl font-bold text-blue-700" x-text="d.ringkasan.mengerjakan"></div>
                        <div class="text-xs text-gray-500">Mengerjakan</div>
                    </div>
                    <div class="p-3 rounded bg-green-50">
                        <div class="text-xl font-bold text-green-700" x-text="d.ringkasan.selesai"></div>
                        <div class="text-xs text-gray-500">Selesai</div>
                    </div>
                </div>
            </div>

            {{-- Daftar per kelas --}}
            <template x-for="k in d.kelas" :key="k.nama">
                <div class="bg-white shadow-sm rounded-lg p-3 sm:p-6 min-w-0">
                    <h4 class="font-semibold text-gray-800">
                        Kelas
                        <span class="ml-1 px-2 py-0.5 text-sm rounded-full bg-blue-100 text-blue-700" x-text="k.nama"></span>
                    </h4>

                    {{-- ===== TAMPILAN HP: kartu per siswa ===== --}}
                    <div class="md:hidden mt-3 space-y-3">
                        <template x-for="r in k.siswa" :key="r.nis">
                            <div class="border border-gray-200 rounded-lg p-3 space-y-3 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-gray-900 break-words" x-text="r.nama"></div>
                                        <div class="text-xs text-gray-500">NIS <span x-text="r.nis"></span></div>
                                    </div>
                                    <div class="shrink-0 text-xs">
                                        <span x-show="r.status === 'belum'"
                                              class="inline-block px-2 py-0.5 rounded-full bg-gray-200 text-gray-600"
                                              x-text="r.ada_token ? 'Belum masuk' : 'Belum ada token'"></span>
                                        <span x-show="r.status === 'mengerjakan'"
                                              class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Mengerjakan</span>
                                        <span x-show="r.status === 'selesai'"
                                              class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700">Selesai</span>
                                    </div>
                                </div>

                                <dl class="text-sm space-y-1.5">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500 shrink-0">Mulai</dt>
                                        <dd class="text-gray-800 text-right min-w-0 break-words" x-text="r.mulai ?? '-'"></dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500 shrink-0">Sisa waktu</dt>
                                        <dd class="font-mono text-right">
                                            <span x-show="r.status === 'mengerjakan'"
                                                  :class="sisa(r) <= 300 ? 'text-red-600 font-bold' : 'text-gray-800'"
                                                  x-text="waktu(sisa(r))"></span>
                                            <span x-show="r.status !== 'mengerjakan'">-</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500 shrink-0">Terjawab</dt>
                                        <dd class="text-gray-800 text-right">
                                            <span x-show="r.status !== 'belum'" x-text="r.terjawab + ' / ' + r.total"></span>
                                            <span x-show="r.status === 'belum'">-</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500 shrink-0">Aktivitas terakhir</dt>
                                        <dd class="text-right">
                                            <span x-show="r.status === 'mengerjakan'"
                                                  :class="diam(r) > 300 ? 'text-red-600 font-semibold' : 'text-gray-800'"
                                                  x-text="lamaDiam(r) + ' lalu'"></span>
                                            <span x-show="r.status !== 'mengerjakan'">-</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-gray-500 shrink-0">Pelanggaran</dt>
                                        <dd class="text-right">
                                            <span x-show="r.status !== 'belum'"
                                                  :class="r.pelanggaran > 0 ? 'px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold' : 'text-gray-800'"
                                                  x-text="d.batas > 0 ? r.pelanggaran + ' / ' + d.batas : r.pelanggaran"></span>
                                            <span x-show="r.status === 'belum'">-</span>
                                        </dd>
                                    </div>
                                </dl>

                                <div class="grid grid-cols-2 gap-2 empty:hidden">
                                    <template x-if="r.url_log">
                                        <a :href="r.url_log"
                                           class="{{ $tombolHp }} {{ $warnaLog }}">Log</a>
                                    </template>
                                    <template x-if="r.url_akhiri">
                                        {{-- Konfirmasi lewat SweetAlert --}}
                                        <form method="POST" :action="r.url_akhiri" class="contents"
                                              data-judul="Akhiri sesi?"
                                              :data-konfirmasi="'Sesi ' + r.nama + ' akan diakhiri sekarang. Jawaban yang sudah masuk akan dinilai.'"
                                              data-tombol="Ya, akhiri">
                                            <input type="hidden" name="_token" :value="csrf()">
                                            <button type="submit"
                                                    class="{{ $tombolHp }} {{ $warnaAkhiri }}">Akhiri sesi</button>
                                        </form>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- ===== TAMPILAN TABLET/DESKTOP: tabel (bisa digeser ke samping di dalam kartu) ===== --}}
                    <div class="hidden md:block overflow-x-auto max-w-full">
                        <table class="w-full text-left text-sm mt-3">
                            <thead>
                                <tr class="border-b text-gray-600">
                                    <th class="py-2 pr-3">NIS</th>
                                    <th class="py-2 pr-3">Nama</th>
                                    <th class="py-2 pr-3">Status</th>
                                    <th class="py-2 pr-3">Mulai</th>
                                    <th class="py-2 pr-3">Sisa waktu</th>
                                    <th class="py-2 pr-3">Terjawab</th>
                                    <th class="py-2 pr-3">Aktivitas terakhir</th>
                                    <th class="py-2 pr-3">Pelanggaran</th>
                                    <th class="py-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="r in k.siswa" :key="r.nis">
                                    <tr class="border-b">
                                        <td class="py-3 pr-3" x-text="r.nis"></td>
                                        <td class="py-3 pr-3 font-medium text-gray-900" x-text="r.nama"></td>
                                        <td class="py-3 pr-3 whitespace-nowrap">
                                            <span x-show="r.status === 'belum'"
                                                  class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600"
                                                  x-text="r.ada_token ? 'Belum masuk' : 'Belum ada token'"></span>
                                            <span x-show="r.status === 'mengerjakan'"
                                                  class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Mengerjakan</span>
                                            <span x-show="r.status === 'selesai'"
                                                  class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Selesai</span>
                                        </td>
                                        <td class="py-3 pr-3" x-text="r.mulai ?? '-'"></td>
                                        <td class="py-3 pr-3 font-mono">
                                            <span x-show="r.status === 'mengerjakan'"
                                                  :class="sisa(r) <= 300 ? 'text-red-600 font-bold' : ''"
                                                  x-text="waktu(sisa(r))"></span>
                                            <span x-show="r.status !== 'mengerjakan'">-</span>
                                        </td>
                                        <td class="py-3 pr-3">
                                            <span x-show="r.status !== 'belum'" x-text="r.terjawab + ' / ' + r.total"></span>
                                            <span x-show="r.status === 'belum'">-</span>
                                        </td>
                                        <td class="py-3 pr-3 whitespace-nowrap">
                                            <span x-show="r.status === 'mengerjakan'"
                                                  :class="diam(r) > 300 ? 'text-red-600 font-semibold' : 'text-gray-600'"
                                                  x-text="lamaDiam(r) + ' lalu'"></span>
                                            <span x-show="r.status !== 'mengerjakan'">-</span>
                                        </td>
                                        <td class="py-3 pr-3 whitespace-nowrap">
                                            <span x-show="r.status !== 'belum'"
                                                  :class="r.pelanggaran > 0 ? 'px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold' : 'text-gray-600'"
                                                  x-text="d.batas > 0 ? r.pelanggaran + ' / ' + d.batas : r.pelanggaran"></span>
                                            <template x-if="r.url_log">
                                                <a :href="r.url_log"
                                                   class="ml-2 {{ $tombol }} {{ $warnaLog }}">Log</a>
                                            </template>
                                            <span x-show="r.status === 'belum'">-</span>
                                        </td>
                                        <td class="py-3 text-right whitespace-nowrap">
                                            <template x-if="r.url_akhiri">
                                                {{-- Konfirmasi lewat SweetAlert --}}
                                                <form method="POST" :action="r.url_akhiri" class="inline"
                                                      data-judul="Akhiri sesi?"
                                                      :data-konfirmasi="'Sesi ' + r.nama + ' akan diakhiri sekarang. Jawaban yang sudah masuk akan dinilai.'"
                                                      data-tombol="Ya, akhiri">
                                                    <input type="hidden" name="_token" :value="csrf()">
                                                    <button type="submit"
                                                            class="{{ $tombol }} {{ $warnaAkhiri }}">Akhiri sesi</button>
                                                </form>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <p class="text-sm text-gray-500" x-show="d.kelas.length === 0">Belum ada peserta.</p>

            {{-- Tombol kembali --}}
            <a href="{{ route('pengawas.ujian.index') }}"
               class="flex sm:inline-flex items-center justify-center px-4 py-2.5 {{ $warnaKembali }} text-white rounded-md text-sm">
                &larr; Kembali ke daftar ujian
            </a>
        </div>
    </div>

    {{-- Notifikasi SweetAlert --}}
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