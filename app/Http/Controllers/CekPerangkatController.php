<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PenjagaPerangkat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CekPerangkatController extends Controller
{
    public function tampil(Request $request): View
    {
        return view('perangkat.cek', [
            'p' => PenjagaPerangkat::periksa($request),
            'aktif' => (bool) config('ujian.penjaga'),
        ]);
    }
}