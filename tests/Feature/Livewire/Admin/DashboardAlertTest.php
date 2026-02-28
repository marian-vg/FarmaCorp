<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Group;
use App\Models\Medicine;
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

    public function test_dashboard_renders_only_medicines_expiring_within_alert_days()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'TestGroup']);

        // Medicine expiring in 10 days
        $prod1 = Product::factory()->create(['name' => 'FastExpiringMed']);
        Medicine::create([
            'product_id' => $prod1->id,
            'group_id' => $group->id,
            'expiration_date' => now()->addDays(10)
        ]);

        // Medicine expiring in 60 days
        $prod2 = Product::factory()->create(['name' => 'LongExpiringMed']);
        Medicine::create([
            'product_id' => $prod2->id,
            'group_id' => $group->id,
            'expiration_date' => now()->addDays(60)
        ]);

        // Default alertDays is 30, so 'LongExpiringMed' shouldn't appear
        // Trigger saveAlertDays to bypass #[Lazy] placeholder rendering
        Livewire::actingAs($admin)->test(Dashboard::class)
            ->call('saveAlertDays')
            ->assertSee('FastExpiringMed')
            ->assertDontSee('LongExpiringMed');
    }

    public function test_admin_can_update_alert_days_setting()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $group = Group::create(['name' => 'TestGroup']);

        $prod = Product::factory()->create(['name' => 'MediumExpiringMed']);
        Medicine::create([
            'product_id' => $prod->id,
            'group_id' => $group->id,
            'expiration_date' => now()->addDays(40)
        ]);

        // Using default 30 days, shouldn't see it
        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertDontSee('MediumExpiringMed')
            ->set('alertDays', 45) // Changing the threshold
            ->call('saveAlertDays')
            ->assertSee('MediumExpiringMed'); // Should see it now

        // Check it persisted to the Settings table
        $this->assertDatabaseHas('settings', [
            'key' => 'alert_days',
            'value' => '45',
        ]);
    }
}
