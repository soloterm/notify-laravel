---
title: Introduction
description: Laravel integration for desktop notifications via terminal OSC sequences.
---

# Notify for Laravel

Notify for Laravel provides seamless integration of [soloterm/notify](/docs/notify) with your Laravel application. Send desktop notifications from Artisan commands, scheduled tasks, queue workers, and anywhere else in your CLI workflow.

## Why Use This Package?

When running long commands, you often switch to other tasks. This package notifies you when:

- Artisan commands complete (or fail)
- Scheduled tasks finish
- Database migrations run
- Tests complete
- Any CLI operation you want to track

## Key Features

### Facade Access

Convenient semantic methods that read well in your code:

```php
use SoloTerm\Notify\Laravel\Facades\Notify;

Notify::success('All tests passed');
Notify::error('Build failed');
Notify::warning('Low disk space');
Notify::info('Background task started');
```

### Artisan Command

Send notifications from the command line or pipe results from other commands:

```bash
php artisan notify "Build complete!"

# Auto-detect success/failure from exit code
php artisan test; php artisan notify -e $?
```

### Scheduler Integration

Notify when scheduled tasks complete:

```php
$schedule->command('backup:run')
    ->daily()
    ->thenNotify('Backup complete!');

$schedule->command('deploy')
    ->withNotification();  // Success or failure
```

### Log Channel

Route log messages to desktop notifications:

```php
Log::channel('notify')->error('Database connection lost!');
```

### Event Listener

Automatically notify when any Artisan command completes:

```php
// In config/notify.php
'events' => [
    'command_finished' => [
        'enabled' => true,
    ],
],
```

## How It Works

This package wraps [soloterm/notify](/docs/notify), which sends notifications via terminal OSC escape sequences. No external dependencies or notification daemons - just escape sequences that modern terminals interpret as desktop notifications.

```
Your Laravel App
      │
      ▼
Notify Facade / Command
      │
      ▼
OSC Escape Sequences → Terminal → Desktop Notification
```

## Quick Start

Install the package:

```bash
composer require soloterm/notify-laravel --dev
```

Send a notification:

```php
use SoloTerm\Notify\Laravel\Facades\Notify;

Notify::send('Hello from Laravel!');
```

## Terminal Support

| Terminal | Support | Notes |
|----------|:-------:|-------|
| **iTerm2** | Full | macOS |
| **Kitty** | Full | Cross-platform |
| **WezTerm** | Full | Cross-platform |
| **Ghostty** | Full | Cross-platform |
| **tmux** | Full | Requires `allow-passthrough on` |
| **Alacritty** | Fallback | Uses system notifications |
| **Terminal.app** | Fallback | Uses osascript |

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| Laravel | 10, 11, or 12 |

## Next Steps

- [Installation](installation) - Setup and verification
- [Configuration](configuration) - All configuration options
- [Facade](facade) - Full Facade API
- [SendsNotifications Trait](trait) - Add notifications to commands
- [Artisan Command](artisan-command) - CLI usage
- [Advanced Features](advanced) - Progress bars, hyperlinks, and more
