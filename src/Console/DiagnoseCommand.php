<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Console;

use Illuminate\Console\Command;
use SoloTerm\Notify\Notify;

class DiagnoseCommand extends Command
{
    protected $signature = 'notify:diagnose
                            {--skip-interactive : Skip interactive tests that require user confirmation}';

    protected $description = 'Diagnose terminal notification capabilities and run interactive tests';

    public function handle(): int
    {
        $this->components->info('Notify Terminal Diagnostics');
        $this->newLine();

        // Environment Detection
        $this->showEnvironmentInfo();
        $this->newLine();

        // Capabilities
        $this->showCapabilities();
        $this->newLine();

        // Skip interactive tests if requested
        if ($this->option('skip-interactive')) {
            $this->components->info('Skipping interactive tests (--skip-interactive)');

            return self::SUCCESS;
        }

        // Interactive Tests
        if ($this->confirm('Run interactive notification tests?', true)) {
            $this->runInteractiveTests();
        }

        return self::SUCCESS;
    }

    protected function showEnvironmentInfo(): void
    {
        $this->components->twoColumnDetail('<fg=cyan>Environment</>');

        $terminal = Notify::getTerminal() ?? 'Unknown';
        $protocol = Notify::getProtocol() ?? 'None';

        $this->components->twoColumnDetail('Terminal', $this->formatTerminal($terminal));
        $this->components->twoColumnDetail('Protocol', $this->formatProtocol($protocol));
        $this->components->twoColumnDetail('PHP SAPI', PHP_SAPI);

        // Environment variables
        $envVars = [
            'TERM' => getenv('TERM') ?: '<fg=gray>not set</>',
            'TERM_PROGRAM' => getenv('TERM_PROGRAM') ?: '<fg=gray>not set</>',
            'COLORTERM' => getenv('COLORTERM') ?: '<fg=gray>not set</>',
        ];

        foreach ($envVars as $var => $value) {
            $this->components->twoColumnDetail($var, $value);
        }

        // Multiplexer detection
        $this->newLine();
        $this->components->twoColumnDetail('<fg=cyan>Multiplexer Detection</>');

        $inTmux = Notify::inTmux();
        $inScreen = Notify::inScreen();

        $this->components->twoColumnDetail(
            'tmux',
            $inTmux ? '<fg=yellow>Yes</> (TMUX=' . substr(getenv('TMUX'), 0, 30) . '...)' : 'No'
        );
        $this->components->twoColumnDetail(
            'GNU Screen',
            $inScreen ? '<fg=yellow>Yes</> (STY=' . getenv('STY') . ')' : 'No'
        );

        if ($inTmux) {
            $this->newLine();
            $this->components->warn('tmux detected. For notifications to work:');
            $this->line('  Add to ~/.tmux.conf: <fg=green>set -g allow-passthrough on</>');
            $this->line('  Then reload: <fg=green>tmux source-file ~/.tmux.conf</>');
        }
    }

    protected function showCapabilities(): void
    {
        $this->components->twoColumnDetail('<fg=cyan>Capabilities</>');

        $capabilities = Notify::capabilities();

        $this->components->twoColumnDetail(
            'Desktop Notifications',
            $capabilities['supports_title'] ?? Notify::canNotify()
                ? '<fg=green>Supported</>'
                : '<fg=red>Not Supported</>'
        );

        $this->components->twoColumnDetail(
            'Notification Titles',
            ($capabilities['supports_title'] ?? false)
                ? '<fg=green>Supported</>'
                : '<fg=yellow>Not Supported</> (message only)'
        );

        $this->components->twoColumnDetail(
            'Urgency Levels',
            ($capabilities['supports_urgency'] ?? false)
                ? '<fg=green>Supported</>'
                : '<fg=gray>Not Supported</>'
        );

        $this->components->twoColumnDetail(
            'Notification IDs',
            ($capabilities['supports_id'] ?? false)
                ? '<fg=green>Supported</>'
                : '<fg=gray>Not Supported</>'
        );

        $this->components->twoColumnDetail(
            'Progress Bars',
            Notify::supportsProgress()
                ? '<fg=green>Supported</>'
                : '<fg=gray>Not Supported</>'
        );

        $this->components->twoColumnDetail(
            'External Fallback',
            Notify::canFallback()
                ? '<fg=green>Available</>'
                : '<fg=gray>Not Available</>'
        );
    }

    protected function runInteractiveTests(): void
    {
        $this->newLine();
        $this->components->info('Interactive Tests');
        $this->line('<fg=yellow>Click away from this terminal window to see notifications.</>');
        $this->newLine();

        $this->countdown(3);

        // Test 1: Basic Notification
        if (Notify::canNotify() || Notify::canFallback()) {
            $this->testBasicNotification();
        } else {
            $this->components->warn('Skipping notification test - not supported');
        }

        // Test 2: Progress Bar
        if (Notify::supportsProgress()) {
            $this->testProgressBar();
        } else {
            $this->line('<fg=gray>Skipping progress bar test - not supported in this terminal</>');
        }

        // Test 3: Semantic notifications
        if (Notify::canNotify() || Notify::canFallback()) {
            $this->testSemanticNotifications();
        }

        // Test 4: External fallback
        if (Notify::canFallback()) {
            $this->testFallback();
        } else {
            $this->newLine();
            $this->line('<fg=gray>Skipping fallback test - no external tools available</>');
        }

        $this->newLine();
        $this->showTroubleshooting();
        $this->newLine();
        $this->components->info('Diagnostics complete!');
    }

    protected function testBasicNotification(): void
    {
        $this->line('Sending basic notification...');
        $sent = Notify::send('This is a test notification from notify:diagnose', 'Notify Test');
        $this->components->twoColumnDetail('Basic Notification', $sent ? '<fg=green>Sent</>' : '<fg=red>Failed</>');
        sleep(3);
    }

    protected function testProgressBar(): void
    {
        $this->newLine();
        $this->line('Testing progress bar (watch the terminal tab/taskbar)...');
        sleep(1);

        // Indeterminate
        $this->line('  <fg=gray>Indeterminate progress...</>');
        Notify::progressIndeterminate();
        sleep(1);

        // Progress through states
        for ($i = 0; $i <= 100; $i += 20) {
            Notify::progress($i);
            $this->output->write("\r  Progress: {$i}%  ");
            usleep(300000); // 300ms
        }
        $this->newLine();

        // Error state
        $this->line('  <fg=gray>Error state (red)...</>');
        Notify::progressError(100);
        sleep(1);

        // Paused state
        $this->line('  <fg=gray>Paused state (yellow)...</>');
        Notify::progressPaused(50);
        sleep(1);

        // Clear
        Notify::progressClear();

        $this->components->twoColumnDetail('Progress Bar', '<fg=green>Complete</>');
        sleep(2);
    }

    protected function testSemanticNotifications(): void
    {
        $this->newLine();
        $this->line('Testing semantic notifications...');
        sleep(1);

        $this->line('  <fg=gray>Success notification...</>');
        Notify::send('This is a success message', config('notify.titles.success', 'Success'));
        sleep(3);

        $this->line('  <fg=gray>Warning notification...</>');
        Notify::send('This is a warning message', config('notify.titles.warning', 'Warning'));
        sleep(3);

        $this->line('  <fg=gray>Error notification (critical urgency)...</>');
        Notify::sendCritical('This is an error message', config('notify.titles.error', 'Error'));
        sleep(3);

        $this->components->twoColumnDetail('Semantic Notifications', '<fg=green>Sent</>');
        sleep(2);
    }

    protected function testFallback(): void
    {
        $this->newLine();
        $this->line('Testing external fallback (system notifications)...');
        $this->line('  <fg=gray>Using system tools (notify-send, osascript) instead of OSC</>');
        sleep(1);

        $sent = Notify::sendExternal('This notification was sent via external fallback', 'Fallback Test');
        $this->components->twoColumnDetail('External Fallback', $sent ? '<fg=green>Sent</>' : '<fg=red>Failed</>');
        sleep(3);
    }

    protected function countdown(int $seconds): void
    {
        for ($i = $seconds; $i > 0; $i--) {
            $this->output->write("\r<fg=gray>{$i}...</>");
            sleep(1);
        }
        $this->output->write("\r        \r"); // Clear the countdown
    }

    protected function showTroubleshooting(): void
    {
        $this->newLine();
        $this->components->warn('Troubleshooting tips:');
        $this->line('');

        $tips = [
            'Check that your terminal supports OSC notifications',
            'Disable "Do Not Disturb" / "Focus Mode", or allow your terminal to send notifications in focus mode',
            'Verify terminal has notification permissions in system settings',
            'If using tmux, ensure "allow-passthrough on" is set',
            'Try forcing a protocol: php artisan notify "test" --protocol=osc777',
        ];

        foreach ($tips as $i => $tip) {
            $this->line('  ' . ($i + 1) . '. ' . $tip);
        }
    }

    protected function formatTerminal(string $terminal): string
    {
        return match ($terminal) {
            'kitty' => '<fg=green>Kitty</> (OSC 99 - full features)',
            'iterm2' => '<fg=green>iTerm2</> (OSC 9)',
            'wezterm' => '<fg=green>WezTerm</> (OSC 777)',
            'ghostty' => '<fg=green>Ghostty</> (OSC 777 + progress)',
            'windows-terminal' => '<fg=green>Windows Terminal</> (progress only)',
            'vte' => '<fg=green>VTE-based</> (GNOME Terminal, etc.)',
            'alacritty' => '<fg=yellow>Alacritty</> (fallback only)',
            'apple-terminal' => '<fg=yellow>Terminal.app</> (fallback only)',
            'Unknown' => '<fg=red>Unknown</>',
            default => $terminal,
        };
    }

    protected function formatProtocol(?string $protocol): string
    {
        return match ($protocol) {
            'osc99' => '<fg=green>OSC 99</> (Kitty - title, urgency, ID)',
            'osc777' => '<fg=green>OSC 777</> (title + message)',
            'osc9' => '<fg=green>OSC 9</> (message only)',
            'None', null => '<fg=red>None</>',
            default => $protocol,
        };
    }
}
