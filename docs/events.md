---
title: Events
description: Automatic notifications when Artisan commands finish.
---

# Event Listener

Notify for Laravel can automatically send desktop notifications whenever Artisan commands complete. This is useful for monitoring all CLI activity without adding notification code to each command.

## Enable the Listener

Enable in `config/notify.php`:

```php
'events' => [
    'command_finished' => [
        'enabled' => true,  // Enable automatic notifications
        'success_title' => 'Command Completed',
        'failure_title' => 'Command Failed',
    ],
],
```

Or via environment variable:

```bash
NOTIFY_ON_COMMAND_FINISHED=true
```

## How It Works

When enabled, the package listens for Laravel's `CommandFinished` event. After any Artisan command completes:

1. The listener checks if the command is excluded
2. Determines success (exit code 0) or failure (non-zero)
3. Sends an appropriate notification

### Success Notification

```
Title: Command Completed
Message: migrate completed successfully
```

### Failure Notification

```
Title: Command Failed
Message: migrate failed (exit code: 1)
Urgency: Critical
```

## Excluded Commands

Some commands shouldn't trigger notifications. Configure exclusions in `config/notify.php`:

```php
'events' => [
    'excluded_commands' => [
        // Built-in exclusions
        'notify',           // Prevent recursion
        'list',
        'help',
        'env',

        // Long-running commands
        'schedule:run',
        'queue:work',
        'queue:listen',

        // Add your own
        'serve',
        'tinker',
        'test',
    ],
],
```

### Default Exclusions

These commands are excluded by default:

| Command | Reason |
|---------|--------|
| `notify` | Prevents notification recursion |
| `list` | Internal/utility command |
| `help` | Internal/utility command |
| `env` | Internal/utility command |
| `schedule:run` | Long-running/continuous |
| `queue:work` | Long-running/continuous |
| `queue:listen` | Long-running/continuous |

## Customizing Titles

Change the notification titles:

```php
'events' => [
    'command_finished' => [
        'enabled' => true,
        'success_title' => 'Artisan Done',      // Custom success title
        'failure_title' => 'Artisan Error',     // Custom failure title
    ],
],
```

## Use Cases

### Development Monitoring

Enable during development to know when any command finishes:

```bash
# In .env.local or .env
NOTIFY_ON_COMMAND_FINISHED=true
```

Now you'll be notified after every:

- `php artisan migrate`
- `php artisan db:seed`
- `php artisan cache:clear`
- Any custom commands

### CI/CD Pipelines

Enable for build commands in deployment scripts:

```bash
export NOTIFY_ON_COMMAND_FINISHED=true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

### Batch Operations

When running multiple commands, know when each completes:

```bash
php artisan db:seed --class=UsersSeeder    # Notification
php artisan db:seed --class=ProductsSeeder # Notification
php artisan db:seed --class=OrdersSeeder   # Notification
```

## Urgency Levels

| Result | Urgency |
|--------|---------|
| Success (exit code 0) | Normal |
| Failure (exit code > 0) | Critical |

Critical notifications may bypass Do Not Disturb settings.

## Combining with Other Features

### With Scheduler Macros

Event listener handles one-off commands. Scheduler macros handle recurring tasks:

```php
// Event listener handles: php artisan migrate
// Scheduler macros handle:
$schedule->command('backup:run')
    ->daily()
    ->withNotification();
```

### With Logging Channel

Use both for comprehensive notification coverage:

```php
// config/logging.php - for specific log messages
'notify' => [
    'driver' => 'custom',
    'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
    'level' => 'error',
],
```

```php
// config/notify.php - for command completion
'events' => [
    'command_finished' => ['enabled' => true],
],
```

## Performance Considerations

The event listener is lightweight:

- Only active in CLI context
- Quick string comparison for exclusions
- No database queries
- Minimal overhead

However, for high-frequency scheduled tasks, consider:

```php
// Add to exclusions if running every minute
'excluded_commands' => [
    'health:check',
    'pulse:check',
],
```

## Conditional Enabling

Enable only in specific environments:

```php
// In config/notify.php
'events' => [
    'command_finished' => [
        'enabled' => env('NOTIFY_ON_COMMAND_FINISHED',
            app()->environment('local')  // Only local by default
        ),
    ],
],
```

Or dynamically in a service provider:

```php
// In AppServiceProvider
public function boot()
{
    if (app()->runningInConsole() && $this->shouldNotify()) {
        config(['notify.events.command_finished.enabled' => true]);
    }
}

private function shouldNotify(): bool
{
    return app()->environment('local')
        || request()->hasHeader('X-Enable-Notifications');
}
```

## Listener Class

The listener is implemented in:

```
SoloTerm\Notify\Laravel\Listeners\NotifyOnCommandFinished
```

It handles the `Illuminate\Console\Events\CommandFinished` event and:

1. Skips excluded commands
2. Formats success/failure message
3. Sets appropriate urgency
4. Sends the notification

## Debugging

If notifications aren't appearing:

1. Check if enabled:
   ```bash
   php artisan tinker
   >>> config('notify.events.command_finished.enabled')
   ```

2. Check if command is excluded:
   ```bash
   >>> config('notify.events.excluded_commands')
   ```

3. Verify terminal support:
   ```bash
   php artisan notify --info
   ```

4. Test manually:
   ```bash
   php artisan migrate; echo "Exit code: $?"
   ```

## Next Steps

- [Facade](facade) - Direct notification API
- [Artisan Command](artisan-command) - Manual notification sending
