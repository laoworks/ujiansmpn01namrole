<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Support\NomorWa;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = [
        User::ROLE_ADMIN,
        User::ROLE_GURU,
        User::ROLE_PENGAWAS,
        User::ROLE_SISWA,
    ];

    public function index(Request $request): View
    {
        $users = User::with('kelas')
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->when($request->q, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")
                    ->orWhere('username', 'like', "%{$s}%")
            ))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User([
                'role' => User::ROLE_SISWA,
                'is_active' => true,
            ]),
            'kelas' => Kelas::orderBy('nama')->get(),
            'aksi' => route('admin.users.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:users,username',
            ],
            'no_wa' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(self::ROLES)],
            'kelas_id' => [
                'nullable',
                'required_if:role,siswa',
                'exists:kelas,id',
            ],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ]);

        $data = $this->rapikanWa($data);

        if ($data['role'] !== User::ROLE_SISWA) {
            $data['kelas_id'] = null;
        }

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'kelas' => Kelas::orderBy('nama')->get(),
            'aksi' => route('admin.users.update', $user),
        ]);
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'no_wa' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(self::ROLES)],
            'kelas_id' => [
                'nullable',
                'required_if:role,siswa',
                'exists:kelas,id',
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ]);

        $data = $this->rapikanWa($data);

        if ($data['role'] !== User::ROLE_SISWA) {
            $data['kelas_id'] = null;
        }

        // Password kosong = tidak diubah
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Admin tidak boleh mengubah role atau menonaktifkan akun sendiri
        if ($user->is($request->user())) {
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun berhasil diperbarui.');
    }

    /**
     * Normalisasi nomor WhatsApp ke format yang didukung NomorWa.
     */
    private function rapikanWa(array $data): array
    {
        if (filled($data['no_wa'] ?? null)) {
            $wa = NomorWa::normalisasi($data['no_wa']);

            if ($wa === null) {
                throw ValidationException::withMessages([
                    'no_wa' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890',
                ]);
            }

            $data['no_wa'] = $wa;
        } else {
            $data['no_wa'] = null;
        }

        return $data;
    }

    public function toggle(
        Request $request,
        User $user
    ): RedirectResponse {
        if ($user->is($request->user())) {
            return back()->withErrors([
                'aksi' => 'Anda tidak bisa menonaktifkan akun sendiri.',
            ]);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->with(
            'status',
            'Akun '.$user->username
                .($user->is_active ? ' diaktifkan.' : ' dinonaktifkan.')
        );
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $baru = Str::password(8, symbols: false);

        $user->update(['password' => $baru]);

        return back()->with('password_baru', [
            'username' => $user->username,
            'password' => $baru,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        // Akun yang sedang dipakai tidak boleh dihapus
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'hapus' => 'Anda tidak bisa menghapus akun Anda sendiri.',
            ]);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            // Akun memiliki data terkait
            return back()->withErrors([
                'hapus' => 'Akun "'.$user->name
                    .'" tidak bisa dihapus karena sudah punya data ujian. '
                    .'Gunakan tombol Nonaktifkan.',
            ]);
        }

        return back()->with(
            'status',
            'Akun "'.$user->name.'" berhasil dihapus.'
        );
    }
}
