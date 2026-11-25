<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NomorWa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportWaController extends Controller
{
    public function form(): View
    {
        return view('admin.import.wa');
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo "\xEF\xBB\xBF";
            echo "nis;wa\r\n";
            echo "1001;081234567890\r\n";
            echo "1002;0852-1111-2222\r\n";
        }, 'template-nomor-wa.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'Pilih file CSV terlebih dahulu.',
            'file.extensions' => 'File harus berformat .csv',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($handle));

        $pemisah = ';';
        if (substr_count($header, ',') > substr_count($header, ';')) {
            $pemisah = ',';
        } elseif (substr_count($header, "\t") > substr_count($header, ';')) {
            $pemisah = "\t";
        }

        $kolom = array_map(fn ($h) => strtolower(trim($h)), str_getcsv(trim($header), $pemisah));

        foreach (['nis', 'wa'] as $wajib) {
            if (! in_array($wajib, $kolom, true)) {
                fclose($handle);

                return back()->withErrors(['file' => "Kolom \"{$wajib}\" tidak ditemukan. Gunakan template yang disediakan."]);
            }
        }

        $diperbarui = 0;
        $dilewati = [];
        $no = 1;

        while (($row = fgetcsv($handle, 0, $pemisah)) !== false) {
            $no++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $d = [];
            foreach ($kolom as $i => $nama) {
                $d[$nama] = trim($row[$i] ?? '');
            }

            $user = User::where('role', User::ROLE_SISWA)->where('username', $d['nis'])->first();

            if (! $user) {
                $dilewati[] = ['baris' => $no, 'nis' => $d['nis'], 'alasan' => 'NIS tidak ditemukan.'];
                continue;
            }

            $wa = NomorWa::normalisasi($d['wa']);

            if ($wa === null) {
                $dilewati[] = ['baris' => $no, 'nis' => $d['nis'], 'alasan' => 'Nomor WhatsApp kosong atau tidak valid.'];
                continue;
            }

            $user->update(['no_wa' => $wa]);
            $diperbarui++;
        }

        fclose($handle);

        return redirect()->route('admin.import.wa.form')
            ->with('hasil', ['diperbarui' => $diperbarui, 'dilewati' => $dilewati]);
    }
}