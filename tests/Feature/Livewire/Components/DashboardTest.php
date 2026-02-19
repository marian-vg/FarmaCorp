<?php

namespace Tests\Feature\Livewire\Components;

use Livewire\Volt\Volt;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_it_can_render(): void
    {
        $component = Volt::test('components.dashboard');

        $component->assertSee('');
    }
}
