@php
    $izinkanBrowser = (bool) config('ujian.izinkan_browser');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perangkat tidak diizinkan</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; min-height: 100vh;
               display: flex; align-items: center; justify-content: center; padding: 16px; }
        .kartu { background: #fff; max-width: 440px; width: 100%; padding: 28px; border-radius: 12px;
                 box-shadow: 0 2px 12px rgba(0,0,0,.08); text-align: center; }
        h1 { font-size: 20px; margin: 0 0 8px; color: #b91c1c; }
        p { color: #374151; font-size: 14px; line-height: 1.5; }
        button { background: #1f2937; color: #fff; border: 0; padding: 10px 18px; border-radius: 8px; cursor: pointer; }
        a { color: #2563eb; font-size: 13px; }
    </style>
</head>
<body>
    <div class="kartu">
        <h1>Perangkat tidak diizinkan</h1>
        <p>
            @if ($izinkanBrowser)
                Ujian hanya bisa dibuka lewat <strong>aplikasi ujian</strong> (HP),
                <strong>Safe Exam Browser</strong> (laptop), atau <strong>Google Chrome</strong>.
                Browser lain (Edge, Firefox, Opera, dan sejenisnya) tidak diizinkan.
            @else
                Ujian hanya bisa dibuka lewat <strong>aplikasi ujian</strong> (HP)
                atau <strong>Safe Exam Browser</strong> (laptop).
            @endif
            Buka ujian dari sana, atau hubungi pengawas.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>

        <p><a href="{{ route('cek.perangkat') }}">Periksa perangkat ini</a></p>
    </div>
</body>
</html>