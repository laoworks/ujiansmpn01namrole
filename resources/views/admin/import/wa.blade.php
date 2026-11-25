<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Nomor WhatsApp</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <ol class="list-decimal list-inside text-gray-700 space-y-1 text-sm">
                    <li>Unduh template, lalu isi kolom <strong>nis</strong> dan <strong>wa</strong> (nomor siswa atau orang tua).</li>
                    <li>Format nomor bebas: <code>081234567890</code>, <code>+62 812-3456-7890</code>, atau <code>6281234567890</code>.</li>
                    <li>Di Excel, format kolom <strong>wa</strong> sebagai <strong>Teks</strong> sebelum mengisi, supaya angka 0 di depan tidak hilang. Nomor yang kehilangan angka 0 tetap dikenali.</li>
                    <li>Simpan sebagai <strong>CSV</strong>, lalu upload. Siswa harus sudah ada di sistem.</li>
                </ol>

                <a href="{{ route('admin.import.wa.template') }}" class="inline-block mt-4 text-blue-600">&darr; Unduh template CSV</a>

                <form method="POST" action="{{ route('admin.import.wa.store') }}" enctype="multipart/form-data"
                      class="mt-4 flex flex-wrap gap-3 items-center">
                    @csrf
                    <input type="file" name="file" accept=".csv,.txt" required class="text-sm">
                    <x-primary-button>Import</x-primary-button>
                </form>
            </div>

            @if (session('hasil'))
                @php($hasil = session('hasil'))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">
                        Diperbarui: {{ $hasil['diperbarui'] }} &middot; Dilewati: {{ count($hasil['dilewati']) }}
                    </h3>

                    @if (count($hasil['dilewati']))
                        <table class="w-full text-left text-sm mt-3">
                            <thead>
                                <tr class="border-b text-gray-600">
                                    <th class="py-2">Baris</th>
                                    <th class="py-2">NIS</th>
                                    <th class="py-2">Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hasil['dilewati'] as $l)
                                    <tr class="border-b">
                                        <td class="py-1">{{ $l['baris'] }}</td>
                                        <td class="py-1">{{ $l['nis'] }}</td>
                                        <td class="py-1">{{ $l['alasan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endif

            <a href="{{ route('admin.users.index') }}" class="inline-block text-gray-600">&larr; Kembali ke Kelola Akun</a>
        </div>
    </div>
</x-app-layout>