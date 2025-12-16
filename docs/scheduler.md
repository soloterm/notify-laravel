---
title: Scheduler
description: Desktop notifications for scheduled tasks.
---

# Scheduler Integration

Notify for Laravel adds macros to Laravel's scheduler, making it easy to get desktop notifications when scheduled tasks complete.

## Available Macros

### thenNotify()

Send a notification after a task completes (success or failure):

```php
// In routes/console.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run')
    ->daily()
    ->thenNotify('Backup complete!');

// With custom title
Schedule::command('reports:generate')
    ->hourly()
    ->thenNotify('Reports ready', 'Reports');
```

### thenNotifySuccess()

Send a notification only when the task succeeds (exit code 0):

```php
Schedule::command('sync:data')
    ->everyFiveMinutes()
    ->thenNotifySuccess('Data synchronized');

// With custom title
Schedule::command('cache:clear')
    ->daily()
    ->thenNotifySuccess('Cache cleared', 'Maintenance');
```

### thenNotifyFailure()

Send a notification only when the task fails (non-zero exit code):

```php
Schedule::command('payments:process')
    ->hourly()
    ->thenNotifyFailure('Payment processing failed!');

// With custom title (uses critical urgency automatically)
Schedule::command('health:check')
    ->everyMinute()
    ->thenNotifyFailure('Health check failed', 'Critical');
```

### withNotification()

Send notifications for both success and failure - the most common use case:

```php
// Uses command name for messages
Schedule::command('deploy:production')
    ->daily()
    ->withNotification();

// Custom messages
Schedule::command('cleanup:logs')
    ->weekly()
    ->withNotification(
        successMessage: 'Cleanup finished',
        failureMessage: 'Cleanup failed'
    );

// Custom messages and title
Schedule::command('db:backup')
    ->daily()
    ->withNotification(
        successMessage: 'Database backed up',
        failureMessage: 'Backup failed - check disk space',
        title: 'Database'
    );
```

## Chaining with Other Callbacks

Notification macros can be combined with other scheduler methods:

```php
Schedule::command('reports:generate')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        // Additional success logic
    })
    ->thenNotify('Daily reports ready');

Schedule::command('queue:restart')
    ->hourly()
    ->evenInMaintenanceMode()
    ->thenNotifyFailure('Queue restart failed');
```

## Examples by Use Case

### Database Backups

```php
Schedule::command('backup:run')
    ->daily()
    ->at('03:00')
    ->withNotification(
        successMessage: 'Backup completed successfully',
        failureMessage: 'Backup failed - immediate attention required',
        title: 'Database Backup'
    );
```

### Queue Workers

```php
// Restart queue workers daily
Schedule::command('queue:restart')
    ->daily()
    ->thenNotifyFailure('Failed to restart queue workers');

// Monitor failed jobs
Schedule::command('queue:failed')
    ->hourly()
    ->thenNotify('Failed jobs report generated', 'Queue');
```

### Report Generation

```php
Schedule::command('reports:daily')
    ->dailyAt('08:00')
    ->withNotification(
        successMessage: 'Daily reports are ready',
        failureMessage: 'Report generation failed'
    );

Schedule::command('reports:weekly')
    ->weeklyOn(1, '09:00')  // Monday at 9 AM
    ->thenNotifySuccess('Weekly summary ready', 'Reports');
```

### Maintenance Tasks

```php
// Cache clearing
Schedule::command('cache:clear')
    ->daily()
    ->thenNotify('Cache cleared');

// Log rotation
Schedule::command('log:clear')
    ->weekly()
    ->thenNotifySuccess('Logs rotated');

// Temporary file cleanup
Schedule::command('cleanup:temp')
    ->daily()
    ->withNotification(
        successMessage: 'Temp files cleaned',
        failureMessage: 'Cleanup failed - disk may be full'
    );
```

### Data Synchronization

```php
Schedule::command('sync:external-api')
    ->everyThirtyMinutes()
    ->withNotification(
        successMessage: 'API sync complete',
        failureMessage: 'API sync failed - check connectivity',
        title: 'External API'
    );
```

### Deployments

```php
Schedule::command('deploy:assets')
    ->environments(['production'])
    ->daily()
    ->withNotification(
        successMessage: 'Assets deployed to CDN',
        failureMessage: 'Asset deployment failed',
        title: 'Deploy'
    );
```

## Urgency Levels

The macros automatically set appropriate urgency:

| Macro | Urgency |
|-------|---------|
| `thenNotify()` | Normal |
| `thenNotifySuccess()` | Normal |
| `thenNotifyFailure()` | Critical |
| `withNotification()` (success) | Normal |
| `withNotification()` (failure) | Critical |

Critical urgency notifications may bypass Do Not Disturb on some systems.

## Default Titles

When no title is provided:

| Macro | Default Title |
|-------|---------------|
| `thenNotify()` | Config: `notify.default_title` (Laravel) |
| `thenNotifySuccess()` | Config: `notify.titles.success` (Success) |
| `thenNotifyFailure()` | Config: `notify.titles.error` (Error) |
| `withNotification()` | Uses success/error titles from config |

## Command Name in Messages

When using `withNotification()` without messages, the command name is used:

```php
Schedule::command('backup:database')
    ->daily()
    ->withNotification();
// Success: "backup:database completed"
// Failure: "backup:database failed"
```

## Important Notes

### Terminal Context

Scheduled tasks run via `schedule:run`, which may not have a terminal attached. Notifications work when:

1. You run `schedule:run` manually in a terminal
2. Your cron runs in an environment with terminal access
3. Fallback to system notifications is enabled

### Testing Scheduled Notifications

Test your scheduled commands manually:

```bash
php artisan backup:run; php artisan notify -e $? \
    --success-message="Backup completed" \
    --failure-message="Backup failed"
```

### Multiple Notifications

You can use multiple notification macros:

```php
Schedule::command('important:task')
    ->daily()
    ->thenNotifySuccess('Task completed')
    ->thenNotifyFailure('Task failed');
```

## Next Steps

- [Logging](logging) - Log channel for notifications
- [Events](events) - Automatic command completion notifications
