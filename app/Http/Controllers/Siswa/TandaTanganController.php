<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Services\TandaTangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TandaTanganController extends Controller
{
    public function simpan(Request $request, Ujian $ujian): RedirectResponse
    {
        $user = $request->user();
        $bag = 'ujian'.$ujian->id;

        abort_unless($user->kelas_id && $ujian->kelas()->whereKey($user->kelas_id)->exists(), 403);

        if ($ujian->status !== 'aktif') {
            return back()->withErrors(['ttd' => 'Ujian ini tidak aktif.'], $bag);
        }

        if (SesiUjian::where('ujian_id', $ujian->id)->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['ttd' => 'Anda sudah memulai ujian ini, tanda tangan tidak bisa diubah.'], $bag);
        }

        $request->validateWithBag($bag, [
            'ttd' => ['required', 'string'],
        ], [
            'ttd.required' => 'Gambar tanda tangan terlebih dahulu.',
        ]);

        $path = TandaTangan::simpan($request->input('ttd'), 'tanda_tangan/peserta');

        if (! $path) {
            return back()->withErrors(['ttd' => 'Tanda tangan tidak valid. Coba gambar ulang.'], $bag);
        }

        $lama = Kehadiran::where('ujian_id', $ujian->id)->where('user_id', $user->id)->first();

        // Menandatangani daftar hadir = hadir
        Kehadiran::updateOrCreate(
            ['ujian_id' => $ujian->id, 'user_id' => $user->id],
            ['hadir' => true, 'ttd_peserta' => $path, 'ttd_at' => now()]
        );

        TandaTangan::hapus($lama?->ttd_peserta);

        return back()->with('status', 'Tanda tangan kehadiran tersimpan.');
    }
}