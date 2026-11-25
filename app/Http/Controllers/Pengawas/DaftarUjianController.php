<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Illuminate\View\View;

class DaftarUjianController extends Controller
{
    public function index(): View
    {
        $ujian = Ujian::with('mapel')
            ->withCount([
                'kelas',
                'token',
                'token as token_dipakai_count' => fn ($q) => $q->whereNotNull('dipakai_at'),
            ])
            ->whereIn('status', ['aktif', 'selesai'])
            ->orderByRaw("status = 'aktif' desc")
            ->orderByDesc('mulai_at')
            ->limit(30)
            ->get();

        return view('pengawas.ujian.index', compact('ujian'));
    }
}