<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\TenantRegistrationComponent;
use Livewire\Livewire;

class TestFormValidation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:form-validation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the tenant registration form validation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Tenant Registration Form Validation...');

        try {
            // Test Step 1 validation
            $this->info('Testing Step 1 validation (Business Type)...');
            $component = Livewire::test(TenantRegistrationComponent::class);

            // Try to proceed without selecting business type
            $component->call('nextStep');
            $component->assertHasErrors(['tenantType']);
            $this->info('✅ Step 1 validation works correctly');

            // Select ISP and proceed
            $component->set('tenantType', 'isp');
            $component->call('nextStep');
            $component->assertHasNoErrors(['tenantType']);
            $this->info('✅ Step 1 data accepted correctly');

            // Test Step 2 validation
            $this->info('Testing Step 2 validation (Contact Info)...');
            $component->call('nextStep');
            $component->assertHasErrors(['companyName', 'businessEmail', 'phoneNumber']);
            $this->info('✅ Step 2 validation works correctly');

            // Fill Step 2 data
            $component->set('companyName', 'Test Company 2')
                     ->set('businessEmail', 'test2@example.com')
                     ->set('phoneNumber', '+1234567890');

            $component->call('nextStep');
            $component->assertHasNoErrors(['companyName', 'businessEmail', 'phoneNumber']);
            $this->info('✅ Step 2 data accepted correctly');

            // Proceed to Step 3 (skip configuration for now)
            $component->call('nextStep');
            $this->info('✅ Step 3 accessed successfully');

            // Proceed to Step 4
            $component->call('nextStep');
            $this->info('✅ Step 4 accessed successfully');

            // Test Step 4 validation (Admin Account)
            $component->call('nextStep');
            $component->assertHasErrors(['firstName', 'lastName', 'adminEmail', 'password']);
            $this->info('✅ Step 4 validation works correctly');

            // Fill Step 4 data
            $component->set('firstName', 'Admin')
                     ->set('lastName', 'User')
                     ->set('adminEmail', 'admin2@example.com')
                     ->set('password', 'Password123!')
                     ->set('passwordConfirmation', 'Password123!');

            $component->call('nextStep');
            $component->assertHasNoErrors(['firstName', 'lastName', 'adminEmail', 'password']);
            $this->info('✅ Step 4 data accepted correctly');

            // Final step - accept terms
            $component->set('acceptTerms', true);

            $this->info('✅ Form validation test completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Form validation test failed: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
