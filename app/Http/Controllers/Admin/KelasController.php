<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        return view('admin.master.index', [
            'judul' => 'Kelas',
            'rute' => 'admin.kelas',
            'tampilSiswa' => true,
            'daftar' => Kelas::withCount('siswa')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:kelas,nama'],
        ]);

        Kelas::create($data);

        return back()->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('admin.master.edit', [
            'judul' => 'Kelas',
            'rute' => 'admin.kelas',
            'item' => $kelas,
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama')->ignore($kelas->id)],
        ]);

        $kelas->update($data);

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if ($kelas->siswa()->exists()) {
            return back()->withErrors(['hapus' => 'Kelas tidak bisa dihapus karena masih memiliki siswa.']);
        }

        $kelas->delete();

        return back()->with('status', 'Kelas berhasil dihapus.');
    }
}