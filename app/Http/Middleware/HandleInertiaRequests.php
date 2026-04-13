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
                    'is_pimpinan'   => $request->user()->isPimpinan(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'pending_verification_count' => fn () => $request->user() && $request->user()->isCdo()
                ? Kegiatan::whereIn('status', ['diajukan', 'diajukan_ulang'])->count()
                : 0,
            'notifications' => fn () => $request->user()
                ? $request->user()->notifications()->latest()->limit(8)->get()->map(function ($notification) {
                    $data = (array) $notification->data;

                    return [
                        'id' => $notification->id,
                        'type' => class_basename($notification->type),
                        'title' => $data['title'] ?? 'Notifikasi',
                        'message' => $data['message'] ?? '',
                        'route' => $data['route'] ?? null,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                    ];
                })->values()
                : [],
            'unread_notification_count' => fn () => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
        ]);
    }
}
