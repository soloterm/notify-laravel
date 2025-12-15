<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Logging;

use Monolog\Level;
use Monolog\Logger;

/**
 * Factory class for creating the notify log channel.
 *
 * Usage in config/logging.php:
 * ```php
 * 'notify' => [
 *     'driver' => 'custom',
 *     'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
 *     'level' => 'warning',
 *     'title' => 'My App',
 * ],
 * ```
 */
class CreateNotifyLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __invoke(array $config): Logger
    {
        $level = $this->parseLevel($config['level'] ?? 'info');
        $title = $config['title'] ?? config('notify.default_title', 'Laravel');
        $maxLength = $config['max_length'] ?? 200;

        $handler = new NotifyLogHandler($level, true, $title, $maxLength);

        return new Logger('notify', [$handler]);
    }

    /**
     * Parse log level string to Monolog Level enum.
     */
    protected function parseLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Info,
        };
    }
}
