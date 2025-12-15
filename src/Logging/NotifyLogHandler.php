<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use SoloTerm\Notify\Notify;

/**
 * Monolog handler that sends log messages as desktop notifications.
 */
class NotifyLogHandler extends AbstractProcessingHandler
{
    /**
     * Default notification title.
     */
    protected string $defaultTitle;

    /**
     * Maximum message length before truncation.
     */
    protected int $maxLength;

    /**
     * Create a new NotifyLogHandler instance.
     *
     * @param  int|string|Level  $level  The minimum logging level at which this handler will be triggered.
     * @param  bool  $bubble  Whether the messages that are handled can bubble up the stack or not.
     * @param  string  $defaultTitle  Default title for notifications.
     * @param  int  $maxLength  Maximum message length before truncation.
     */
    public function __construct(
        int|string|Level $level = Level::Info,
        bool $bubble = true,
        string $defaultTitle = 'Laravel Log',
        int $maxLength = 200
    ) {
        parent::__construct($level, $bubble);
        $this->defaultTitle = $defaultTitle;
        $this->maxLength = $maxLength;
    }

    /**
     * Write a log record to the notification system.
     */
    protected function write(LogRecord $record): void
    {
        $urgency = $this->mapLevelToUrgency($record->level);
        $title = $this->formatTitle($record);
        $message = $this->formatMessage($record);

        Notify::send($message, $title, $urgency);
    }

    /**
     * Map Monolog log level to notification urgency.
     */
    protected function mapLevelToUrgency(Level $level): int
    {
        return match ($level) {
            Level::Emergency,
            Level::Alert,
            Level::Critical,
            Level::Error => Notify::URGENCY_CRITICAL,

            Level::Warning,
            Level::Notice => Notify::URGENCY_NORMAL,

            Level::Info,
            Level::Debug => Notify::URGENCY_LOW,
        };
    }

    /**
     * Format the notification title.
     */
    protected function formatTitle(LogRecord $record): string
    {
        $levelName = $record->level->name;

        return sprintf('%s [%s]', $this->defaultTitle, $levelName);
    }

    /**
     * Format the notification message.
     */
    protected function formatMessage(LogRecord $record): string
    {
        $message = $record->message;

        // Include channel if not the notify channel itself
        if ($record->channel !== 'notify') {
            $message = "[{$record->channel}] {$message}";
        }

        // Truncate long messages
        if (strlen($message) > $this->maxLength) {
            $message = substr($message, 0, $this->maxLength - 3) . '...';
        }

        return $message;
    }
}
