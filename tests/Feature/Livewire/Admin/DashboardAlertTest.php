<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Group;
use App\Models\Medicine;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_dashboard_renders_only_batches_expiring_within_alert_days_with_positive_stock()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'TestGroup']);

        $prod1 = Product::factory()->create(['name' => 'FastExpiringMed']);
        $med1 = Medicine::create(['product_id' => $prod1->id, 'group_id' => $group->id]);

        // Batch expiring in 10 days, quantity > 0 MUST appear
        Batch::create([
            'medicine_id' => $med1->product_id,
            'batch_number' => 'LT-001',
            'expiration_date' => now()->addDays(10),
            'initial_quantity' => 10,
            'current_quantity' => 10,
        ]);

        // Batch expiring in 10 days, quantity == 0 MUST NOT appear
        Batch::create([
            'medicine_id' => $med1->product_id,
            'batch_number' => 'LT-002-ZERO',
            'expiration_date' => now()->addDays(10),
            'initial_quantity' => 10,
            'current_quantity' => 0,
        ]);

        $prod2 = Product::factory()->create(['name' => 'LongExpiringMed']);
        $med2 = Medicine::create(['product_id' => $prod2->id, 'group_id' => $group->id]);

        // Batch expiring in 60 days, quantity > 0 MUST NOT appear (exceeds default 30 days)
        Batch::create([
            'medicine_id' => $med2->product_id,
            'batch_number' => 'LT-003',
            'expiration_date' => now()->addDays(60),
            'initial_quantity' => 10,
            'current_quantity' => 10,
        ]);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->call('saveAlertDays')
            ->assertSee('LT-001')
            ->assertDontSee('LT-002-ZERO')
            ->assertDontSee('LT-003');
    }

    public function test_admin_can_update_alert_days_setting()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'TestGroup']);

        $prod = Product::factory()->create(['name' => 'MediumExpiringMed']);
        $med = Medicine::create(['product_id' => $prod->id, 'group_id' => $group->id]);

        Batch::create([
            'medicine_id' => $med->product_id,
            'batch_number' => 'LT-MED',
            'expiration_date' => now()->addDays(40),
            'initial_quantity' => 10,
            'current_quantity' => 10,
        ]);

        // Using default 30 days, shouldn't see it
        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertDontSee('LT-MED')
            ->set('alertDays', 45) // Changing the threshold
            ->call('saveAlertDays')
            ->assertSee('LT-MED'); // Should see it now

        // Check it persisted to the Settings table
        $this->assertDatabaseHas('settings', [
            'key' => 'alert_days',
            'value' => '45',
        ]);
    }
}
