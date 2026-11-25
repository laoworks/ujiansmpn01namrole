<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Ujian extends Model
{
    /** Panjang kode masuk (QR). Token pribadi siswa 6 karakter, jadi keduanya mudah dibedakan. */
    public const KODE_PANJANG = 8;

    /** Tidak dipakai lagi (kode sekarang tetap). Dibiarkan agar kode lain yang merujuknya tidak error. */
    public const KODE_PERIODE = 45;

    /** Tanpa 0/O dan 1/I/L, sama seperti token pribadi */
    private const KODE_HURUF = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected $table = 'ujian';

    protected $fillable = [
        'guru_id',
        'mapel_id',
        'judul',
        'durasi_menit',
        'mulai_at',      // awal sesi pertama (diisi otomatis dari jadwal)
        'selesai_at',    // akhir sesi terakhir (diisi otomatis dari jadwal)
        'acak_soal',
        'tampil_nilai',
        'batas_pelanggaran',
        'status',

        // Pengiriman hasil ke WhatsApp
        'kirim_wa',
        'wa_waktu',
        'wa_detail',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'selesai_at' => 'datetime',
            'acak_soal' => 'boolean',
            'tampil_nilai' => 'boolean',

            // Pengaturan WhatsApp
            'kirim_wa' => 'boolean',
        ];
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function soal(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'ujian_soal')
            ->withPivot('urutan');
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'ujian_kelas');
    }

    public function token(): HasMany
    {
        return $this->hasMany(TokenUjian::class);
    }

    /** Jadwal sesi ujian (Sesi 1, 2, 3, ...), urut menurut waktu */
    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalUjian::class)->orderBy('urutan');
    }

    /** Daftar sesi; ujian tanpa baris jadwal dianggap satu sesi dari mulai_at sampai selesai_at */
    public function jadwalEfektif(): Collection
    {
        if ($this->jadwal->isNotEmpty()) {
            return $this->jadwal;
        }

        return collect([new JadwalUjian([
            'urutan' => 1,
            'mulai_at' => $this->mulai_at,
            'selesai_at' => $this->selesai_at,
        ])]);
    }

    /** Sesi yang sedang berjalan saat ini (null jika tidak ada) */
    public function jadwalBerjalan(): ?JadwalUjian
    {
        $now = now();

        return $this->jadwalEfektif()
            ->first(fn ($j) => $now->between($j->mulai_at, $j->selesai_at));
    }

    /** Sesi berikutnya yang belum mulai (null jika semua sudah lewat) */
    public function jadwalBerikutnya(): ?JadwalUjian
    {
        return $this->jadwalEfektif()->first(fn ($j) => $j->mulai_at->isFuture());
    }

    /**
     * Kode masuk ujian: TETAP (tidak berganti), dihitung dari APP_KEY dan id ujian.
     * Tidak disimpan di database dan tidak berubah selama APP_KEY sama.
     * Parameter $geser dipertahankan agar pemanggil lama (mis. TokenController) tidak error;
     * nilainya diabaikan.
     */
    public function kodeMasuk(int $geser = 0): string
    {
        $hash = hash_hmac('sha256', 'kode-tetap|'.$this->id, (string) config('app.key'), true);

        $kode = '';
        for ($i = 0; $i < self::KODE_PANJANG; $i++) {
            $kode .= self::KODE_HURUF[ord($hash[$i]) % strlen(self::KODE_HURUF)];
        }

        return $kode;
    }

    /** Kode sah bila cocok dengan kode tetap ujian ini */
    public function kodeMasukSah(string $kode): bool
    {
        return hash_equals($this->kodeMasuk(), strtoupper(trim($kode)));
    }

    /** Sisa detik sebelum kode berganti. Kode kini tetap, jadi nilainya hanya penanda. */
    public function sisaDetikKode(): int
    {
        return self::KODE_PERIODE;
    }

    /**
     * Label status yang ditampilkan:
     * menggabungkan status dan jadwal sesi.
     */
    public function getFaseAttribute(): string
    {
        if ($this->status === 'draft') {
            return 'Draft';
        }

        if ($this->status === 'selesai') {
            return 'Ditutup';
        }

        if ($this->jadwalBerjalan()) {
            return 'Berlangsung';
        }

        if (now()->gt($this->jadwalEfektif()->last()->selesai_at)) {
            return 'Waktu berakhir';
        }

        // Sebelum sesi pertama, atau jeda antar sesi
        return 'Menunggu jadwal';
    }
}