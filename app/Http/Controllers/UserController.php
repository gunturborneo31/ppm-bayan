<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Index', [
            'users' => User::with('divisi:id,nama')->latest()->get(),
            'divisis' => Divisi::where('status', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['superadmin', 'divisi', 'pimpinan'])],
            'divisi_id' => ['nullable', 'exists:divisis,id', 'required_if:role,divisi'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['role'] === 'superadmin') {
            $data['divisi_id'] = null;
        }

        User::create($data);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['superadmin', 'divisi', 'pimpinan'])],
            'divisi_id' => ['nullable', 'exists:divisis,id', 'required_if:role,divisi'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->isSuperadmin() && $data['role'] !== 'superadmin') {
            $remainingSuperadmin = User::where('role', 'superadmin')->where('id', '!=', $user->id)->exists();
            if (!$remainingSuperadmin) {
                return back()->withErrors(['role' => 'Minimal harus ada satu superadmin.']);
            }
        }

        if ($data['role'] === 'superadmin') {
            $data['divisi_id'] = null;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->withErrors(['user' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }

        if ($user->isSuperadmin()) {
            $remainingSuperadmin = User::where('role', 'superadmin')->where('id', '!=', $user->id)->exists();
            if (!$remainingSuperadmin) {
                return back()->withErrors(['user' => 'Minimal harus ada satu superadmin.']);
            }
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
