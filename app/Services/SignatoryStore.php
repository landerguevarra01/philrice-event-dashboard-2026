<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SignatoryStore
{
    /**
     * The signatory printed on certificates. Falls back to the defaults in config/event.php
     * only if the row is missing.
     *
     * @return array{name: string, role: string}
     */
    public function get(): array
    {
        $row = DB::table('signatory')->where('id', 1)->first();

        if ($row === null) {
            return [
                'name' => (string) config('event.signatory.name'),
                'role' => (string) config('event.signatory.role'),
            ];
        }

        return ['name' => (string) $row->name, 'role' => (string) $row->role];
    }

    /**
     * Changes the existing row only. It never adds one.
     *
     * @return array{ok: bool, message: string}
     */
    public function update(string $name, string $role): array
    {
        if (DB::table('signatory')->where('id', 1)->doesntExist()) {
            return ['ok' => false, 'message' => 'The signatory record is missing. Run php artisan migrate.'];
        }

        DB::table('signatory')->where('id', 1)->update([
            'name' => $name,
            'role' => $role,
            'updated_at' => now(),
        ]);

        return ['ok' => true, 'message' => 'Signatory updated.'];
    }
}
