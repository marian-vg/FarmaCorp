<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // IMPORTANTE: Agregamos esta

// --- COMANDOS ARTISAN ---

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// --- AGENDA DE TAREAS (SCHEDULER) ---

// El Scheduler revisa cada minuto, pero solo ejecuta si se cumple el horario Y la condición 'when'
Schedule::command('app:auto-backup')
    ->dailyAt('00:00')
    ->when(function () {
        $freq = Setting::where('key', 'backup_frequency')->first()?->value;
        return $freq === 'daily';
    });

Schedule::command('app:auto-backup')
    ->weeklyOn(0, '00:00') // Domingos
    ->when(function () {
        $freq = Setting::where('key', 'backup_frequency')->first()?->value;
        return $freq === 'weekly';
    });

Schedule::command('app:auto-backup')
    ->monthlyOn(1, '00:00') // Día 1 de cada mes
    ->when(function () {
        $freq = Setting::where('key', 'backup_frequency')->first()?->value;
        return $freq === 'monthly';
    });