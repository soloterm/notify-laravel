<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Listeners;

use Illuminate\Console\Events\CommandFinished;
use SoloTerm\Notify\Notify;

/**
 * Event listener that sends notifications when Artisan commands finish.
 */
class NotifyOnCommandFinished
{
    /**
     * Handle the CommandFinished event.
     */
    public function handle(CommandFinished $event): void
    {
        // Skip the notify command itself to avoid recursion
        if ($event->command === 'notify') {
            return;
        }

        // Check if command should be excluded
        $excludedCommands = config('notify.events.excluded_commands', []);
        if (in_array($event->command, $excludedCommands, true)) {
            return;
        }

        $exitCode = $event->exitCode ?? 0;
        $isSuccess = $exitCode === 0;

        $config = config('notify.events.command_finished', []);

        $title = $isSuccess
            ? ($config['success_title'] ?? 'Command Completed')
            : ($config['failure_title'] ?? 'Command Failed');

        $message = $isSuccess
            ? sprintf('%s completed successfully', $event->command ?? 'Command')
            : sprintf('%s failed (exit code: %d)', $event->command ?? 'Command', $exitCode);

        $urgency = $isSuccess ? Notify::URGENCY_NORMAL : Notify::URGENCY_CRITICAL;

        Notify::send($message, $title, $urgency);
    }
}
