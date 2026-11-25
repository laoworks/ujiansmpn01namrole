<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MapelController extends Controller
{
    public function index(): View
    {
        return view('admin.master.index', [
            'judul' => 'Mata Pelajaran',
            'rute' => 'admin.mapel',
            'tampilSiswa' => false,
            'daftar' => Mapel::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:mapel,nama'],
        ]);

        Mapel::create($data);

        return back()->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel): View
    {
        return view('admin.master.edit', [
            'judul' => 'Mata Pelajaran',
            'rute' => 'admin.mapel',
            'item' => $mapel,
        ]);
    }

    public function update(Request $request, Mapel $mapel): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('mapel', 'nama')->ignore($mapel->id)],
        ]);

        $mapel->update($data);

        return redirect()->route('admin.mapel.index')->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
{
    $dipakai = \Illuminate\Support\Facades\DB::table('soal')->where('mapel_id', $mapel->id)->exists()
        || \Illuminate\Support\Facades\DB::table('ujian')->where('mapel_id', $mapel->id)->exists();

    if ($dipakai) {
        return back()->withErrors(['hapus' => 'Mapel tidak bisa dihapus karena sudah dipakai soal atau ujian.']);
    }

    $mapel->delete();

    return back()->with('status', 'Mata pelajaran berhasil dihapus.');
}
}