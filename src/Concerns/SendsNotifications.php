<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Concerns;

use SoloTerm\Notify\Notify;

/**
 * Add desktop notification capabilities to any Artisan command.
 */
trait SendsNotifications
{
    protected function notify(string $message, ?string $title = null): bool
    {
        if (! Notify::canNotify()) {
            return false;
        }

        $title ??= config('notify.default_title', 'Laravel');

        return Notify::send($message, $title);
    }

    protected function notifySuccess(string $message, ?string $title = null): bool
    {
        return $this->notify($message, $title ?? '✓ Success');
    }

    protected function notifyError(string $message, ?string $title = null): bool
    {
        return $this->notify($message, $title ?? '✗ Error');
    }

    protected function notifyWarning(string $message, ?string $title = null): bool
    {
        return $this->notify($message, $title ?? '⚠ Warning');
    }

    protected function canNotify(): bool
    {
        return Notify::canNotify();
    }
}
