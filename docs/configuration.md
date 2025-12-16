---
title: Configuration
description: Configure Notify for Laravel to match your needs.
---

# Configuration

Notify for Laravel works out of the box with sensible defaults. This page covers all available configuration options.

## Publishing the Config File

```bash
php artisan vendor:publish --tag=notify-config
```

This creates `config/notify.php`:

```php
return [
    // Default title when none specified
    'default_title' => env('NOTIFY_DEFAULT_TITLE', 'Laravel'),

    // Titles for semantic methods
    'titles' => [
        'success' => env('NOTIFY_TITLE_SUCCESS', 'Success'),
        'error' => env('NOTIFY_TITLE_ERROR', 'Error'),
        'warning' => env('NOTIFY_TITLE_WARNING', 'Warning'),
        'info' => env('NOTIFY_TITLE_INFO', 'Info'),
    ],

    // Force a specific protocol (null for auto-detect)
    'force_protocol' => env('NOTIFY_FORCE_PROTOCOL'),

    // Enable external fallback (notify-send, osascript)
    'enable_fallback' => env('NOTIFY_ENABLE_FALLBACK', true),

    // Event listener configuration
    'events' => [
        'command_finished' => [
            'enabled' => env('NOTIFY_ON_COMMAND_FINISHED', false),
            'success_title' => 'Command Completed',
            'failure_title' => 'Command Failed',
        ],
        'excluded_commands' => [
            'notify', 'list', 'help', 'env',
            'schedule:run', 'queue:work', 'queue:listen',
        ],
    ],

    // Logging channel defaults
    'logging' => [
        'default_level' => 'warning',
        'max_length' => 200,
    ],
];
```

## Configuration Options

### default_title

The default notification title when none is specified.

```php
'default_title' => env('NOTIFY_DEFAULT_TITLE', 'Laravel'),
```

Used by:
- `Notify::send('Message')` - when no title provided
- Scheduler macros when no title provided

### titles

Default titles for semantic notification methods.

```php
'titles' => [
    'success' => env('NOTIFY_TITLE_SUCCESS', 'Success'),
    'error' => env('NOTIFY_TITLE_ERROR', 'Error'),
    'warning' => env('NOTIFY_TITLE_WARNING', 'Warning'),
    'info' => env('NOTIFY_TITLE_INFO', 'Info'),
],
```

Used by:
- `Notify::success()`, `Notify::error()`, `Notify::warning()`, `Notify::info()`
- `$this->notifySuccess()`, `$this->notifyError()`, etc. in commands using the trait

### force_protocol

Force a specific OSC protocol instead of auto-detection.

```php
'force_protocol' => env('NOTIFY_FORCE_PROTOCOL'),
```

Valid values:
- `null` - Auto-detect (default)
- `'osc9'` - iTerm2 protocol
- `'osc777'` - WezTerm/Ghostty protocol
- `'osc99'` - Kitty protocol (most features)

Use this when:
- Terminal detection isn't working correctly
- You want consistent behavior across environments
- Testing notification features

### enable_fallback

Enable fallback to system notification tools when OSC isn't supported.

```php
'enable_fallback' => env('NOTIFY_ENABLE_FALLBACK', true),
```

Fallback tools used:
- **macOS**: `osascript` (AppleScript notifications)
- **Linux**: `notify-send` (libnotify)
- **Windows**: PowerShell notifications

Set to `false` if you only want pure OSC notifications.

### events.command_finished

Configure automatic notifications when Artisan commands complete.

```php
'events' => [
    'command_finished' => [
        'enabled' => env('NOTIFY_ON_COMMAND_FINISHED', false),
        'success_title' => 'Command Completed',
        'failure_title' => 'Command Failed',
    ],
],
```

When enabled, every Artisan command (except excluded ones) triggers a notification:
- Exit code 0: Success notification with normal urgency
- Non-zero: Failure notification with critical urgency

See [Events](events) for full documentation.

### events.excluded_commands

Commands that won't trigger automatic notifications.

```php
'events' => [
    'excluded_commands' => [
        'notify',           // Prevent recursion
        'list',
        'help',
        'env',
        'schedule:run',     // Long-running
        'queue:work',       // Long-running
        'queue:listen',     // Long-running
    ],
],
```

Add any commands you don't want notifications for:

```php
'excluded_commands' => [
    // Default exclusions...
    'serve',      // Development server
    'tinker',     // Interactive shell
    'test',       // May be too frequent
    'horizon',    // Long-running
],
```

### logging

Defaults for the notify log channel.

```php
'logging' => [
    'default_level' => 'warning',
    'max_length' => 200,
],
```

These are defaults when creating the log channel. You can override them per-channel in `config/logging.php`. See [Logging](logging) for full documentation.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `NOTIFY_DEFAULT_TITLE` | Laravel | Default notification title |
| `NOTIFY_TITLE_SUCCESS` | Success | Title for `Notify::success()` |
| `NOTIFY_TITLE_ERROR` | Error | Title for `Notify::error()` |
| `NOTIFY_TITLE_WARNING` | Warning | Title for `Notify::warning()` |
| `NOTIFY_TITLE_INFO` | Info | Title for `Notify::info()` |
| `NOTIFY_FORCE_PROTOCOL` | null | Force osc9, osc777, or osc99 |
| `NOTIFY_ENABLE_FALLBACK` | true | Use system tools if OSC unsupported |
| `NOTIFY_ON_COMMAND_FINISHED` | false | Auto-notify on command completion |

## Runtime Configuration

You can also configure the Notify service at runtime via the Facade:

```php
use SoloTerm\Notify\Laravel\Facades\Notify;

// Force a protocol
Notify::forceProtocol('osc777');

// Disable fallback
Notify::disableFallback();

// Set default urgency
Notify::setDefaultUrgency(Notify::URGENCY_LOW);

// Reset all runtime configuration
Notify::reset();
```

Runtime configuration is useful for:
- Testing different protocols
- Temporarily changing behavior
- Environment-specific overrides in service providers

## Environment-Specific Configuration

### Development

```bash
# .env.local
NOTIFY_ON_COMMAND_FINISHED=true
NOTIFY_DEFAULT_TITLE="Dev"
```

### Production

```bash
# .env.production
NOTIFY_ENABLE_FALLBACK=false
NOTIFY_ON_COMMAND_FINISHED=false
```

### Testing

```php
// In tests, you might want to disable notifications entirely
// The package only registers in CLI context, so web tests are unaffected
```

## Next Steps

- [Facade](facade) - Full Facade API
- [Events](events) - Automatic command notifications
- [Logging](logging) - Log channel configuration
