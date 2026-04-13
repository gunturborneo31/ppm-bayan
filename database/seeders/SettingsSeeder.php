<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'planning_locked'],
            [
                'value' => false,
                'label' => 'Kunci Perencanaan',
                'description' => 'Mengunci input perencanaan untuk semua user non-superadmin',
            ]
        );
    }
}
