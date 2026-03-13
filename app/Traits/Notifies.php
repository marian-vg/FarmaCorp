<?php

namespace App\Traits;

trait Notifies
{
    /**
     * Emite una notificación al frontend para que la capture el listener global.
     *
     * @param  string  $type  success|error
     */
    public function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }
}
