<?php

namespace App\Traits;

trait Notifies
{
    /**
     * Emite una notificación al frontend para que la capture el listener global.
     *
     * @param string $message
     * @param string $type success|danger|warning|info
     * @return void
     */
    public function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }
}
