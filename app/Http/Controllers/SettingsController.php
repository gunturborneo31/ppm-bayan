<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Show settings page (superadmin only)
     */
    public function index()
    {
        $settings = Setting::tableExists()
            ? Setting::orderBy('key')->get()
            : collect();
        
        // Format settings for display
        $formattedSettings = $settings->map(function ($setting) {
            return [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'label' => $setting->label,
                'description' => $setting->description,
            ];
        });

        return Inertia::render('Settings/Index', [
            'settings' => $formattedSettings,
            'planningLocked' => Setting::isPlanningLocked(),
            'settingsTableReady' => Setting::tableExists(),
        ]);
    }

    /**
     * Update a setting
     */
    public function update(Request $request, Setting $setting)
    {
        if (!Setting::tableExists()) {
            return back()->withErrors([
                'settings' => 'Tabel pengaturan belum tersedia. Jalankan migrasi terlebih dahulu.',
            ]);
        }

        $validated = $request->validate([
            'value' => 'required',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $oldValue = $setting->value;
        $setting->update($validated);

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'settings',
            'action' => 'update',
            'subject_type' => Setting::class,
            'subject_id' => $setting->id,
            'description' => "Updated setting: {$setting->key}",
            'field' => 'value',
            'old_value' => json_encode($oldValue),
            'new_value' => json_encode($validated['value']),
        ]);

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }

    /**
     * Toggle planning lock status
     */
    public function togglePlanningLock(Request $request)
    {
        if (!Setting::tableExists()) {
            return back()->withErrors([
                'settings' => 'Tabel pengaturan belum tersedia. Jalankan migrasi terlebih dahulu.',
            ]);
        }

        $locked = !Setting::isPlanningLocked();
        Setting::lockPlanning($locked);

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'settings',
            'action' => $locked ? 'lock' : 'unlock',
            'subject_type' => Setting::class,
            'subject_id' => 0,
            'description' => "Perencanaan " . ($locked ? "dikunci" : "dibuka") . " untuk user non-superadmin",
        ]);

        return back()->with('success', 'Status kunci perencanaan berhasil diperbarui');
    }
}
