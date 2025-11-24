<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncAdminPermissions extends Command
{
    protected $signature   = 'permissions:sync-admin
                            {--show : List the permissions that will be assigned}';

    protected $description = 'Grant the “admin” role every permission in the system';

    public function handle(): int
    {
        $role = Role::firstWhere('name', 'super admin');

        if (! $role) {
            $this->error('Role “admin” does not exist.');
            return self::FAILURE;
        }

        $permissions = Permission::pluck('name')->sort()->values();

        if ($this->option('show')) {
            $this->table(['Permission'], $permissions->map(fn ($p) => [$p]));
            return self::SUCCESS;
        }

        $role->syncPermissions($permissions);

        $this->info("✅  Assigned {$permissions->count()} permissions to “admin”.");

        return self::SUCCESS;
    }
}