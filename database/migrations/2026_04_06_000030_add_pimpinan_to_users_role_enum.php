<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','divisi','pimpinan') NOT NULL DEFAULT 'divisi'");
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement('ALTER TABLE users RENAME TO users_old');

            DB::statement("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL,
                email_verified_at DATETIME,
                password VARCHAR NOT NULL,
                role VARCHAR NOT NULL DEFAULT 'divisi' CHECK (role IN ('superadmin','divisi','pimpinan')),
                divisi_id INTEGER,
                remember_token VARCHAR,
                created_at DATETIME,
                updated_at DATETIME,
                UNIQUE (email),
                FOREIGN KEY(divisi_id) REFERENCES divisis(id) ON DELETE SET NULL
            )");

            DB::statement("INSERT INTO users (id, name, email, email_verified_at, password, role, divisi_id, remember_token, created_at, updated_at)
                SELECT id, name, email, email_verified_at, password, role, divisi_id, remember_token, created_at, updated_at
                FROM users_old");

            DB::statement('DROP TABLE users_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role = 'divisi' WHERE role = 'pimpinan'");
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','divisi') NOT NULL DEFAULT 'divisi'");
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::transaction(function () {
            DB::statement("UPDATE users SET role = 'divisi' WHERE role = 'pimpinan'");
            DB::statement('ALTER TABLE users RENAME TO users_old');

            DB::statement("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL,
                email_verified_at DATETIME,
                password VARCHAR NOT NULL,
                role VARCHAR NOT NULL DEFAULT 'divisi' CHECK (role IN ('superadmin','divisi')),
                divisi_id INTEGER,
                remember_token VARCHAR,
                created_at DATETIME,
                updated_at DATETIME,
                UNIQUE (email),
                FOREIGN KEY(divisi_id) REFERENCES divisis(id) ON DELETE SET NULL
            )");

            DB::statement("INSERT INTO users (id, name, email, email_verified_at, password, role, divisi_id, remember_token, created_at, updated_at)
                SELECT id, name, email, email_verified_at, password, role, divisi_id, remember_token, created_at, updated_at
                FROM users_old");

            DB::statement('DROP TABLE users_old');
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
