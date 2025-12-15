<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Console;

use Illuminate\Console\Command;
use SoloTerm\Notify\Notify;

class NotifyCommand extends Command
{
    protected $signature = 'notify
                            {message? : The notification message}
                            {--t|title= : Optional notification title}
                            {--p|protocol= : Force a specific protocol (osc9, osc777, osc99)}
                            {--e|exit-code= : Exit code from previous command (0=success, non-zero=failure)}
                            {--success-message=Command completed successfully : Message for success (exit code 0)}
                            {--failure-message=Command failed : Message for failure (non-zero exit code)}
                            {--success-title=✓ Success : Title for success notifications}
                            {--failure-title=✗ Failed : Title for failure notifications}
                            {--info : Show terminal detection info without sending}';

    protected $description = 'Send a desktop notification via terminal OSC escape sequences';

    public function handle(): int
    {
        if ($this->option('info')) {
            return $this->showInfo();
        }

        if (! Notify::canNotify()) {
            $this->components->error('Desktop notifications are not supported in this terminal.');
            $this->newLine();
            $this->showInfo();

            return self::FAILURE;
        }

        if ($protocol = $this->option('protocol')) {
            if (! in_array($protocol, ['osc9', 'osc777', 'osc99'])) {
                $this->components->error("Invalid protocol: {$protocol}. Use osc9, osc777, or osc99.");

                return self::FAILURE;
            }
            Notify::forceProtocol($protocol);
        }

        // Handle exit code mode
        if ($this->option('exit-code') !== null) {
            return $this->handleExitCode((int) $this->option('exit-code'));
        }

        // Standard message mode
        $message = $this->argument('message');

        if (empty($message)) {
            $this->components->error('Message is required unless using --exit-code.');

            return self::FAILURE;
        }

        $title = $this->option('title') ?? config('notify.default_title', 'Laravel');

        $sent = Notify::send($message, $title);

        if ($sent) {
            $this->components->info('Notification sent.');

            return self::SUCCESS;
        }

        $this->components->error('Failed to send notification.');

        return self::FAILURE;
    }

    protected function handleExitCode(int $exitCode): int
    {
        $isSuccess = $exitCode === 0;

        if ($isSuccess) {
            $message = $this->argument('message') ?: $this->option('success-message');
            $title = $this->option('title') ?: $this->option('success-title');
        } else {
            $message = $this->argument('message') ?: $this->option('failure-message');
            $title = $this->option('title') ?: $this->option('failure-title');

            // Include exit code in message if using default
            if (! $this->argument('message')) {
                $message .= " (exit code: {$exitCode})";
            }
        }

        $sent = Notify::send($message, $title);

        if ($sent) {
            $status = $isSuccess ? 'success' : 'failure';
            $this->components->info("Notification sent ({$status}).");

            return self::SUCCESS;
        }

        $this->components->error('Failed to send notification.');

        return self::FAILURE;
    }

    protected function showInfo(): int
    {
        $terminal = Notify::getTerminal() ?? 'Unknown';
        $protocol = Notify::getProtocol() ?? 'None';
        $canNotify = Notify::canNotify() ? '<fg=green>Yes</>' : '<fg=red>No</>';
        $inTmux = Notify::inTmux() ? '<fg=yellow>Yes</>' : 'No';
        $inScreen = Notify::inScreen() ? '<fg=yellow>Yes</>' : 'No';

        $this->components->twoColumnDetail('Terminal', $terminal);
        $this->components->twoColumnDetail('Protocol', $protocol);
        $this->components->twoColumnDetail('Can Notify', $canNotify);
        $this->components->twoColumnDetail('In tmux', $inTmux);
        $this->components->twoColumnDetail('In Screen', $inScreen);

        if (Notify::inTmux() && ! Notify::canNotify()) {
            $this->newLine();
            $this->components->warn('tmux detected. Ensure "allow-passthrough on" is set in ~/.tmux.conf');
        }

        return self::SUCCESS;
    }
}
