@php
    /*
    |--------------------------------------------------------------------------
    | Gaya bersama (sama dengan halaman Hasil via WhatsApp dan Absensi).
    | Semua class ditulis utuh (jangan disambung) agar terbaca Tailwind.
    |--------------------------------------------------------------------------
    */

    // Kartu, judul, dan teks bantuan
    $kartu      = 'bg-white shadow-sm rounded-lg p-4 sm:p-6';
    $judulKartu = 'text-base font-semibold text-gray-900';
    $bantuan    = 'text-xs text-gray-500';

    // Badge
    $badge      = 'inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full';
    $badgeAbu   = $badge . ' bg-gray-100 text-gray-700';
    $badgeHijau = $badge . ' bg-green-100 text-green-700';
    $badgeBiru  = $badge . ' bg-blue-100 text-blue-700';
    $badgeMerah = $badge . ' bg-red-100 text-red-700';

    // Isian formulir
    $isian = 'block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500';
    $centang = 'rounded border-gray-300 text-blue-600 focus:ring-blue-500';

    // Tombol: dasar bersama
    $fokus = 'transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 '
           . 'disabled:opacity-50 disabled:cursor-not-allowed';

    // Tombol kecil (aksi cepat di atas tabel) dan tombol utama (bawah form)
    $tombol      = 'inline-flex items-center justify-center px-3 py-1.5 text-white rounded-md text-xs font-medium ' . $fokus;
    $tombolUtama = 'inline-flex items-center justify-center px-4 py-2 text-white rounded-md text-sm font-medium ' . $fokus;

    // Warna tombol aksi cepat
    $warnaSemua  = 'bg-green-600 hover:bg-green-700';
    $warnaTtd    = 'bg-teal-600 hover:bg-teal-700';
    $warnaMasuk  = 'bg-blue-600 hover:bg-blue-700';
    $warnaKosong = 'bg-gray-800 hover:bg-gray-700';

    // Warna tombol utama
    $warnaSimpan  = 'bg-green-600 hover:bg-green-700';
    $warnaHadir   = 'bg-teal-600 hover:bg-teal-700';
    $warnaBa      = 'bg-purple-600 hover:bg-purple-700';
    $warnaKembali = 'bg-gray-800 hover:bg-gray-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Absensi &amp; Berita Acara: Kelas {{ $kelas->nama }}</h2>
    </x-slot>

    <script>
        function absensiForm(init) {
            return {
                hadir: init.hadir,
                masuk: init.masuk,
                ttd: init.ttd,
                total: init.total,

                get jumlahHadir() { return Object.values(this.hadir).filter(Boolean).length; },
                get jumlahTidak() { return this.total - this.jumlahHadir; },

                semua() { Object.keys(this.hadir).forEach((k) => { this.hadir[k] = true; }); },
                kosong() { Object.keys(this.hadir).forEach((k) => { this.hadir[k] = false; }); },
                dariMasuk() { this.masuk.forEach((id) => { if (id in this.hadir) this.hadir[id] = true; }); },
                dariTtd() { this.ttd.forEach((id) => { if (id in this.hadir) this.hadir[id] = true; }); },
            };
        }
    </script>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            {{-- Notifikasi sukses/error ditangani SweetAlert (lihat skrip di bawah) --}}

            <form method="POST" action="{{ route('absensi.simpan', [$ujian, $kelas]) }}"
                  x-data="absensiForm(@js(['hadir' => (object) $awal, 'masuk' => $masuk, 'ttd' => $sudahTtd, 'total' => $siswa->count()]))"
                  data-konfirmasi="Simpan absensi dan berita acara kelas {{ $kelas->nama }}?"
                  class="space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')

                {{-- Berita acara --}}
                <div class="{{ $kartu }}">
                    <h3 class="{{ $judulKartu }}">Berita acara</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $ujian->judul }} &middot; {{ $ujian->mapel->nama }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                        <div>
                            <x-input-label for="ruang" value="Ruang" />
                            <x-text-input id="ruang" name="ruang" type="text" class="block mt-1 w-full"
                                :value="old('ruang', $ba->ruang)" required />
                        </div>
                        <div>
                            <x-input-label for="tanggal" value="Tanggal pelaksanaan" />
                            <input id="tanggal" name="tanggal" type="date" required
                                   value="{{ old('tanggal', ($ba->tanggal ?? now())->format('Y-m-d')) }}"
                                   class="{{ $isian }}">
                        </div>
                        <div>
                            <x-input-label for="pukul_mulai" value="Dari pukul" />
                            <input id="pukul_mulai" name="pukul_mulai" type="time" required
                                   value="{{ old('pukul_mulai', $ba->pukul_mulai) }}"
                                   class="{{ $isian }}">
                        </div>
                        <div>
                            <x-input-label for="pukul_selesai" value="Sampai pukul" />
                            <input id="pukul_selesai" name="pukul_selesai" type="time" required
                                   value="{{ old('pukul_selesai', $ba->pukul_selesai) }}"
                                   class="{{ $isian }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label for="pengawas1_nama" value="Pengawas I: nama" />
                            <x-text-input id="pengawas1_nama" name="pengawas1_nama" type="text" class="block mt-1 w-full"
                                :value="old('pengawas1_nama', $ba->pengawas1_nama)" />
                        </div>
                        <div>
                            <x-input-label for="pengawas1_nip" value="Pengawas I: NIP" />
                            <x-text-input id="pengawas1_nip" name="pengawas1_nip" type="text" class="block mt-1 w-full"
                                :value="old('pengawas1_nip', $ba->pengawas1_nip)" />
                        </div>
                        <div>
                            <x-input-label for="pengawas2_nama" value="Pengawas II: nama" />
                            <x-text-input id="pengawas2_nama" name="pengawas2_nama" type="text" class="block mt-1 w-full"
                                :value="old('pengawas2_nama', $ba->pengawas2_nama)" />
                        </div>
                        <div>
                            <x-input-label for="pengawas2_nip" value="Pengawas II: NIP" />
                            <x-text-input id="pengawas2_nip" name="pengawas2_nip" type="text" class="block mt-1 w-full"
                                :value="old('pengawas2_nip', $ba->pengawas2_nip)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        @foreach ([1, 2] as $n)
                            <div>
                                <x-input-label :value="'Tanda tangan Pengawas '.($n === 1 ? 'I' : 'II')" />

                                @if ($ttdPengawas[$n])
                                    <div class="mt-1 p-2 border border-gray-200 rounded-md bg-white inline-block">
                                        <img src="{{ $ttdPengawas[$n] }}" alt="Tanda tangan tersimpan" class="h-16">
                                    </div>
                                    <label class="flex items-center gap-2 mt-2 text-xs text-gray-600">
                                        <input type="checkbox" name="hapus_ttd{{ $n }}" value="1" class="{{ $centang }}">
                                        Hapus tanda tangan tersimpan
                                    </label>
                                    <p class="{{ $bantuan }}">Gambar ulang di bawah untuk menggantinya.</p>
                                @endif

                                <div class="mt-2">
                                    <x-ttd-pad :name="'ttd_pengawas'.$n" :tinggi="130" />
                                </div>
                                <x-input-error :messages="$errors->get('ttd_pengawas'.$n)" class="mt-1" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <x-input-label for="catatan" value="Catatan selama ujian berlangsung" />
                        <textarea id="catatan" name="catatan" rows="4" class="{{ $isian }}">{{ old('catatan', $ba->catatan) }}</textarea>
                    </div>
                </div>

                {{-- Absensi peserta --}}
                <div class="{{ $kartu }}">
                    <h3 class="{{ $judulKartu }}">Absensi peserta</h3>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="{{ $badgeAbu }}">Seharusnya: <strong class="ml-1" x-text="total"></strong></span>
                        <span class="{{ $badgeHijau }}">Hadir: <strong class="ml-1" x-text="jumlahHadir"></strong></span>
                        <span class="{{ $badgeMerah }}">Tidak hadir: <strong class="ml-1" x-text="jumlahTidak"></strong></span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" @click="semua()" class="{{ $tombol }} {{ $warnaSemua }}">Tandai semua hadir</button>
                        <button type="button" @click="dariTtd()" class="{{ $tombol }} {{ $warnaTtd }}">Centang yang sudah tanda tangan</button>
                        <button type="button" @click="dariMasuk()" class="{{ $tombol }} {{ $warnaMasuk }}">Centang yang sudah masuk ujian</button>
                        <button type="button" @click="kosong()" class="{{ $tombol }} {{ $warnaKosong }}">Kosongkan</button>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    <th class="px-3 py-2 w-10">No</th>
                                    <th class="px-3 py-2">NIS</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2 text-center">Hadir</th>
                                    <th class="px-3 py-2">Keterangan (bila tidak hadir)</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($siswa as $s)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-700">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2 text-gray-700">{{ $s->username }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $s->name }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox" name="hadir[{{ $s->id }}]" value="1"
                                                   x-model="hadir[{{ $s->id }}]" class="{{ $centang }}">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" name="keterangan[{{ $s->id }}]" maxlength="100"
                                                   value="{{ old('keterangan.'.$s->id, $kehadiran->get($s->id)?->keterangan) }}"
                                                   placeholder="Sakit / izin / alpa"
                                                   class="w-full min-w-[10rem] border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if (in_array($s->id, $sudahTtd))
                                                <span class="{{ $badgeHijau }}">Sudah TTD</span>
                                            @endif
                                            @if (in_array($s->id, $masuk))
                                                <span class="{{ $badgeBiru }}">Sudah masuk</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-10 text-center text-sm text-gray-500">
                                            Belum ada siswa aktif di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
                    <button type="submit" class="{{ $tombolUtama }} {{ $warnaSimpan }}">Simpan</button>
                    <a href="{{ route('absensi.cetak.hadir', [$ujian, $kelas]) }}" target="_blank" rel="noopener"
                       class="{{ $tombolUtama }} {{ $warnaHadir }}">Cetak daftar hadir</a>
                    <a href="{{ route('absensi.cetak.ba', [$ujian, $kelas]) }}" target="_blank" rel="noopener"
                       class="{{ $tombolUtama }} {{ $warnaBa }}">Cetak berita acara</a>
                    <a href="{{ route('absensi.index', $ujian) }}"
                       class="{{ $tombolUtama }} {{ $warnaKembali }}">&larr; Kembali</a>
                </div>
                <p class="{{ $bantuan }}">
                    Simpan dulu sebelum mencetak, karena cetakan memakai data yang sudah tersimpan.
                    Muat ulang halaman ini sebelum menyimpan kalau ada siswa yang baru saja tanda tangan,
                    supaya centangnya tidak tertimpa.
                </p>
            </form>
        </div>
    </div>

    {{-- SweetAlert2. Hapus baris <script src> ini kalau layout sudah memuatnya. --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script>
        // Warna disamakan dengan tombol di halaman (Tailwind blue-600 / gray-500)
        const Notif = Swal.mixin({
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            customClass: { popup: 'rounded-lg', confirmButton: 'text-sm', cancelButton: 'text-sm' },
        });

        // Notifikasi dari session / validasi
        @if (session('status'))
            Notif.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @js(session('status')),
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif

        @if ($errors->any())
            Notif.fire({
                icon: 'error',
                title: 'Gagal menyimpan',
                // Semua pesan error ditampilkan sebagai daftar
                html: '<ul style="text-align:left;list-style:disc;padding-left:1.25rem">'
                    + @js($errors->all()).map(function (m) {
                        const li = document.createElement('li');
                        li.textContent = m; // textContent supaya aman dari HTML
                        return li.outerHTML;
                    }).join('')
                    + '</ul>',
                confirmButtonText: 'Tutup',
            });
        @endif

        // Dialog konfirmasi untuk form ber-atribut data-konfirmasi
        document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                Notif.fire({
                    icon: 'question',
                    title: 'Konfirmasi',
                    text: form.dataset.konfirmasi,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(function (hasil) {
                    if (! hasil.isConfirmed) return;

                    // Cegah klik ganda selama halaman diproses
                    form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
                    form.submit(); // submit() langsung tidak memicu listener lagi
                });
            });
        });
    </script>
</x-app-layout>