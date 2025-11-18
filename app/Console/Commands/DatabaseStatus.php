<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;

class DatabaseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show database status for tenant registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗄️  Database Status');
        $this->line('================');

        $tenantCount = Tenant::count();
        $userCount = User::count();

        $this->line('Total Tenants: ' . $tenantCount);
        $this->line('Total Users: ' . $userCount);
        $this->line('');

        if ($tenantCount > 0) {
            $this->info('📋 Tenants:');
            $tenants = Tenant::all(['id', 'name', 'slug', 'type', 'email', 'status']);
            foreach ($tenants as $tenant) {
                $this->line("  #{$tenant->id}: {$tenant->name} ({$tenant->slug}) - {$tenant->type} - {$tenant->email} - {$tenant->status}");
            }
            $this->line('');
        }

        if ($userCount > 0) {
            $this->info('👥 Users:');
            $users = User::with('tenant')->get(['id', 'tenant_id', 'first_name', 'last_name', 'email', 'role']);
            foreach ($users as $user) {
                $tenantName = $user->tenant ? $user->tenant->name : 'No Tenant';
                $fullName = trim($user->first_name . ' ' . $user->last_name) ?: $user->name;
                $this->line("  #{$user->id}: {$fullName} ({$user->email}) - {$user->role} - Tenant: {$tenantName}");
            }
        }

        $this->line('');
        $this->info('✅ Registration system is ready to use!');
        $this->line('Visit: http://127.0.0.1:8000 to test the registration form');
    }
}
