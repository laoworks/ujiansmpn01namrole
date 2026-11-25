<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Services\SoalImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoalImportController extends Controller
{
    public function form()
    {
        $this->izinkan();

        return view('guru.soal.import', [
            'mapel' => DB::table('mapel')->orderBy('nama')->get(),
        ]);
    }

    public function template(string $jenis)
{
    $this->izinkan();
    abort_unless(in_array($jenis, ['xlsx', 'docx'], true), 404);

    $path = storage_path("app/templates/template_soal.{$jenis}");
    abort_unless(file_exists($path), 404, 'File template belum tersedia. Hubungi admin.');

    return response()->download($path, "template_soal.{$jenis}");
}

    public function store(Request $request, SoalImportService $service)
    {
        $this->izinkan();

        $request->validate([
            'mapel_id' => ['required', 'integer', 'exists:mapel,id'],
            'file'     => ['required', 'file', 'mimes:xlsx,docx', 'max:5120'], // 5 MB
        ], [
            'file.mimes' => 'File harus berformat .docx (Word) atau .xlsx (Excel). Gunakan template yang disediakan.',
            'file.max'   => 'Ukuran file maksimal 5 MB.',
        ]);

        $hasil = $service->import(
            $request->file('file'),
            (int) $request->input('mapel_id'),
            (int) $request->user()->id
        );

        if ($hasil['errors']) {
            return back()
                ->withInput($request->only('mapel_id'))
                ->with('import_errors', $hasil['errors']);
        }

        return back()->with('success', "{$hasil['berhasil']} soal berhasil diimpor.");
    }

    /** Hanya guru dan admin. Sesuaikan dengan middleware role di aplikasi Anda. */
    private function izinkan(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['guru', 'admin'], true), 403);
    }
}