<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportSiswaController extends Controller
{
    public function form(): View
    {
        return view('admin.import.siswa');
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo "\xEF\xBB\xBF"; // BOM supaya Excel membaca UTF-8 dengan benar
            echo "nis;nama;kelas;password\r\n";
            echo "1001;Budi Santoso;X-1;\r\n";
            echo "1002;Siti Aminah;X-1;\r\n";
        }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Pilih file CSV terlebih dahulu.',
            'file.extensions' => 'File harus berformat .csv',
        ]);

        set_time_limit(300); // menyimpan password (hash) untuk banyak siswa butuh waktu

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        // Baris pertama = judul kolom
        $header = fgets($handle);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

        $pemisah = ';';
        if (substr_count($header, ',') > substr_count($header, ';')) {
            $pemisah = ',';
        } elseif (substr_count($header, "\t") > substr_count($header, ';')) {
            $pemisah = "\t";
        }

        $kolom = array_map(fn ($h) => strtolower(trim($h)), str_getcsv(trim($header), $pemisah));

        foreach (['nis', 'nama', 'kelas'] as $wajib) {
            if (! in_array($wajib, $kolom, true)) {
                fclose($handle);

                return back()->withErrors([
                    'file' => "Kolom \"{$wajib}\" tidak ditemukan. Gunakan template yang disediakan.",
                ]);
            }
        }

        $dibuat = [];
        $dilewati = [];
        $kelasCache = [];
        $no = 1;

        while (($row = fgetcsv($handle, 0, $pemisah)) !== false) {
            $no++;

            // Lewati baris kosong
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $d = [];
            foreach ($kolom as $i => $nama) {
                $d[$nama] = trim($row[$i] ?? '');
            }
            $d['password'] ??= '';

            $v = Validator::make($d, [
                'nis' => ['required', 'max:50', 'alpha_dash'],
                'nama' => ['required', 'max:100'],
                'kelas' => ['required', 'max:50'],
                'password' => ['nullable', 'min:6'],
            ], [
                'nis.alpha_dash' => 'NIS hanya boleh huruf, angka, strip, atau garis bawah.',
                'password.min' => 'Password minimal 6 karakter.',
            ]);

            if ($v->fails()) {
                $dilewati[] = ['baris' => $no, 'nis' => $d['nis'], 'alasan' => $v->errors()->first()];
                continue;
            }

            if (User::where('username', $d['nis'])->exists()) {
                $dilewati[] = ['baris' => $no, 'nis' => $d['nis'], 'alasan' => 'NIS sudah terdaftar.'];
                continue;
            }

            $kelasCache[$d['kelas']] ??= Kelas::firstOrCreate(['nama' => $d['kelas']])->id;

            $acak = $d['password'] === '';
            $password = $acak ? sprintf('%06d', random_int(0, 999999)) : $d['password'];

            User::create([
                'name' => $d['nama'],
                'username' => $d['nis'],
                'password' => $password,
                'role' => User::ROLE_SISWA,
                'kelas_id' => $kelasCache[$d['kelas']],
                'is_active' => true,
            ]);

            $dibuat[] = [
                'nis' => $d['nis'],
                'nama' => $d['nama'],
                'kelas' => $d['kelas'],
                'password' => $acak ? $password : '(sesuai file)',
            ];
        }

        fclose($handle);

        return redirect()->route('admin.import.siswa.form')
            ->with('hasil', ['dibuat' => $dibuat, 'dilewati' => $dilewati]);
    }
}