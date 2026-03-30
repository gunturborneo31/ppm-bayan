<?php

namespace App\Http\Middleware;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'            => $request->user()->id,
                    'name'          => $request->user()->name,
                    'email'         => $request->user()->email,
                    'role'          => $request->user()->role,
                    'divisi_id'     => $request->user()->divisi_id,
                    'is_superadmin' => $request->user()->isSuperadmin(),
                    'is_cdo'        => $request->user()->isCdo(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'pending_verification_count' => fn () => $request->user() && $request->user()->isCdo()
                ? Kegiatan::where('status', 'diajukan')->count()
                : 0,
        ]);
    }
}
