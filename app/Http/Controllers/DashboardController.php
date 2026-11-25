<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Arahkan ke dashboard sesuai role */
    public function index(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_GURU => redirect()->route('guru.dashboard'),
            User::ROLE_PENGAWAS => redirect()->route('pengawas.dashboard'),
            default => redirect()->route('siswa.dashboard'),
        };
    }

    public function admin(): View
    {
        return $this->tampil('Dashboard Admin', [
            'Kelola akun guru, pengawas, dan siswa',
            'Kelola kelas dan mata pelajaran',
        ], [
            'Kelola Akun' => route('admin.users.index'),
            'Import Siswa' => route('admin.import.siswa.form'),
            'Kelola Kelas' => route('admin.kelas.index'),
            'Kelola Mata Pelajaran' => route('admin.mapel.index'),
        ]);
    }

    private function tampil(
        string $judul,
        array $menu,
        array $links = []
    ): View {
        return view('dashboards.index', compact('judul', 'menu', 'links'));
    }

    
public function guru(): View
{
    return $this->tampil('Dashboard Guru', [
        'Bank soal (pilihan ganda, kompleks, benar/salah)',
        'Atur ujian dan jadwal',
    ], [
        'Bank Soal' => route('guru.soal.index'),
        'Ujian' => route('guru.ujian.index'),
    ]);
}

public function pengawas(): View
{
    return $this->tampil('Dashboard Pengawas', [
        'Sebelum ujian: isi absensi peserta dan berita acara',
        'Selama ujian: pantau siswa, reset token, dan lihat log pelanggaran',
        'Setelah ujian: lengkapi catatan berita acara dan cetak',
    ], [
        'Ujian, Absensi & Berita Acara' => route('pengawas.ujian.index'),
    ]);
}

    public function siswa(): View
    {
        return $this->tampil('Dashboard Siswa', [
            'Daftar ujian yang tersedia',
            'Masukkan token untuk mulai ujian',
        ]);
    }
   
}