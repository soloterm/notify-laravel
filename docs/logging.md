---
title: Logging
description: Send log messages as desktop notifications.
---

# Notify Log Channel

Route Laravel log messages to desktop notifications. Perfect for alerting on errors during CLI operations like queue workers, scheduled tasks, and artisan commands.

## Setup

Add the notify channel to your `config/logging.php`:

```php
'channels' => [
    // Your existing channels...

    'notify' => [
        'driver' => 'custom',
        'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
        'level' => 'warning',  // Minimum level to notify
        'title' => 'My App',   // Notification title
    ],
],
```

## Basic Usage

Log to the notify channel:

```php
use Illuminate\Support\Facades\Log;

// Send a notification
Log::channel('notify')->error('Database connection lost!');
Log::channel('notify')->warning('High memory usage detected');
Log::channel('notify')->info('Task completed');
```

## Log Level Filtering

Only messages at or above the configured level trigger notifications:

```php
// In config/logging.php
'notify' => [
    'driver' => 'custom',
    'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
    'level' => 'warning',  // Only warning, error, critical, etc.
],
```

With `level' => 'warning'`:

```php
Log::channel('notify')->debug('Ignored');   // No notification
Log::channel('notify')->info('Ignored');    // No notification
Log::channel('notify')->warning('Sent');    // Notification sent
Log::channel('notify')->error('Sent');      // Notification sent
```

## Urgency Mapping

Log levels automatically map to notification urgency:

| Log Level | Urgency |
|-----------|---------|
| emergency, alert, critical, error | Critical |
| warning, notice | Normal |
| info, debug | Low |

Critical urgency notifications may bypass Do Not Disturb on some systems.

## Stack Integration

Add notify to a logging stack for dual logging:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'notify'],
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
    ],

    'notify' => [
        'driver' => 'custom',
        'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
        'level' => 'error',  // Only errors and above
        'title' => 'My App',
    ],
],
```

Now errors go to both the log file and desktop notification:

```php
Log::error('Payment failed');  // Written to file AND notified
```

## Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `level` | info | Minimum log level to notify |
| `title` | Laravel Log | Notification title prefix |
| `max_length` | 200 | Truncate messages longer than this |

### Custom Title

```php
'notify' => [
    'driver' => 'custom',
    'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
    'title' => 'Production Server',
],
```

Notifications appear as: "Production Server [ERROR]"

### Message Length

Long messages are automatically truncated:

```php
'notify' => [
    'driver' => 'custom',
    'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
    'max_length' => 100,  // Shorter notifications
],
```

## Use Cases

### Queue Worker Errors

Monitor your queue workers:

```php
// In a job
public function handle()
{
    try {
        // Job logic...
    } catch (\Exception $e) {
        Log::channel('notify')->error('Job failed: ' . $e->getMessage());
        throw $e;
    }
}
```

Run queue worker in a terminal to see notifications:

```bash
php artisan queue:work
```

### Scheduled Task Monitoring

```php
// In a scheduled command
public function handle()
{
    try {
        $this->processData();
        Log::channel('notify')->info('Data processed successfully');
    } catch (\Exception $e) {
        Log::channel('notify')->error('Data processing failed: ' . $e->getMessage());
        return self::FAILURE;
    }

    return self::SUCCESS;
}
```

### Database Connection Issues

```php
// In a service
public function executeQuery()
{
    try {
        return DB::select($this->query);
    } catch (\PDOException $e) {
        Log::channel('notify')->critical('Database connection failed');
        throw $e;
    }
}
```

### Memory/Resource Alerts

```php
// In a long-running command
$memoryUsage = memory_get_usage(true) / 1024 / 1024;

if ($memoryUsage > 256) {
    Log::channel('notify')->warning("High memory usage: {$memoryUsage}MB");
}
```

## Multiple Notify Channels

Create different channels for different contexts:

```php
'channels' => [
    'notify-critical' => [
        'driver' => 'custom',
        'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
        'level' => 'critical',
        'title' => 'CRITICAL',
    ],

    'notify-all' => [
        'driver' => 'custom',
        'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
        'level' => 'debug',
        'title' => 'Debug',
    ],
],
```

## CLI-Only Behavior

The notify log channel is designed for CLI usage. In web requests:

- No terminal is attached
- Notifications are silently skipped
- No errors are thrown

This makes it safe to include in logging stacks:

```php
// Safe for both web and CLI
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily', 'notify'],
],
```

In web requests, only `daily` receives logs. In CLI, both receive logs.

## Environment-Specific Configuration

Use different logging stacks per environment:

```php
// In config/logging.php
'default' => env('LOG_CHANNEL', 'stack'),

'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => explode(',', env('LOG_STACK', 'daily')),
    ],
],
```

Then in `.env`:

```bash
# Development - include notifications
LOG_STACK=daily,notify

# Production - just files
LOG_STACK=daily
```

## Next Steps

- [Events](events) - Automatic notifications on command completion
- [Facade](facade) - Direct notification API
