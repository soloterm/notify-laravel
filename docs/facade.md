---
title: Facade
description: Using the Notify Facade in your Laravel application.
---

# Notify Facade

The `Notify` Facade provides a clean, expressive API for sending desktop notifications from your Laravel application.

## Basic Usage

```php
use SoloTerm\Notify\Laravel\Facades\Notify;

// Simple message
Notify::send('Task complete!');

// Message with title
Notify::send('All tests passed', 'Tests');
```

## Semantic Methods

Use semantic methods for common notification types. These use configured titles and appropriate urgency levels:

```php
// Success - normal urgency
Notify::success('Build deployed');        // Title: "Success"
Notify::success('Deployed', 'Production'); // Custom title

// Error - critical urgency
Notify::error('Database connection lost'); // Title: "Error"
Notify::error('Query failed', 'MySQL');    // Custom title

// Warning - normal urgency
Notify::warning('Disk space low');         // Title: "Warning"
Notify::warning('High memory', 'Server');  // Custom title

// Info - low urgency
Notify::info('Background sync started');   // Title: "Info"
Notify::info('Started', 'Worker');         // Custom title
```

### Default Titles

Configure default titles in `config/notify.php`:

```php
'titles' => [
    'success' => 'Success',   // Notify::success()
    'error' => 'Error',       // Notify::error()
    'warning' => 'Warning',   // Notify::warning()
    'info' => 'Info',         // Notify::info()
],
```

## Urgency Levels

Control notification importance:

```php
// Normal urgency (default)
Notify::send('Message', 'Title');

// Low urgency - may be deferred by the OS
Notify::sendLow('Background task done');

// Critical urgency - may bypass Do Not Disturb
Notify::sendCritical('Server down!');

// With explicit urgency constant
Notify::send('Alert', 'Title', Notify::URGENCY_CRITICAL);
```

### Urgency Constants

| Constant | Value | Behavior |
|----------|-------|----------|
| `URGENCY_LOW` | 0 | May be deferred |
| `URGENCY_NORMAL` | 1 | Standard notification |
| `URGENCY_CRITICAL` | 2 | High priority, may bypass DND |

## Fallback Options

When OSC notifications aren't supported:

```php
// Fall back to terminal bell
Notify::sendOrBell('Task done');

// Fall back to system notification tools
Notify::sendAny('Important message');

// Just the bell character
Notify::bell();
```

## Checking Capabilities

```php
// Check if OSC notifications are supported
if (Notify::canNotify()) {
    Notify::send('Hello!');
}

// Check if fallback is available
if (Notify::canFallback()) {
    Notify::sendExternal('Using system notifications');
}

// Get terminal and protocol info
$terminal = Notify::getTerminal();  // "iTerm2", "Kitty", etc.
$protocol = Notify::getProtocol();  // "osc9", "osc777", "osc99"

// Get full capabilities array
$caps = Notify::capabilities();
```

## Configuration Methods

```php
// Force a specific protocol
Notify::forceProtocol('osc777');
Notify::forceProtocol(null);  // Reset to auto-detect

// Control fallback behavior
Notify::enableFallback();
Notify::disableFallback();

// Set default urgency for all notifications
Notify::setDefaultUrgency(Notify::URGENCY_LOW);

// Reset all configuration
Notify::reset();
```

## Complete API Reference

### Notification Methods

| Method | Description |
|--------|-------------|
| `send($message, $title?, $urgency?, $id?)` | Send notification |
| `sendLow($message, $title?)` | Low urgency notification |
| `sendCritical($message, $title?)` | Critical urgency notification |
| `sendOrBell($message, $title?)` | Send or fall back to bell |
| `sendAny($message, $title?, $urgency?)` | Send via any available method |
| `sendExternal($message, $title?, $urgency?)` | Use system notification tools |
| `bell()` | Send terminal bell |
| `close($id)` | Close notification by ID (OSC 99 only) |

### Semantic Methods

| Method | Urgency | Default Title |
|--------|---------|---------------|
| `success($message, $title?)` | Normal | "Success" |
| `error($message, $title?)` | Critical | "Error" |
| `warning($message, $title?)` | Normal | "Warning" |
| `info($message, $title?)` | Low | "Info" |

### Capability Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `canNotify()` | bool | OSC notification support |
| `canFallback()` | bool | External fallback available |
| `supportsProgress()` | bool | Progress bar support |
| `getTerminal()` | ?string | Detected terminal name |
| `getProtocol()` | ?string | Active OSC protocol |
| `capabilities()` | array | Full capability info |
| `inTmux()` | bool | Running inside tmux |
| `inScreen()` | bool | Running inside GNU Screen |

### Configuration Methods

| Method | Description |
|--------|-------------|
| `forceProtocol($protocol)` | Force osc9/osc777/osc99 |
| `enableFallback($enabled = true)` | Enable external fallback |
| `disableFallback()` | Disable external fallback |
| `setDefaultUrgency($urgency)` | Set default urgency |
| `reset()` | Reset all configuration |

## Next Steps

- [SendsNotifications Trait](trait) - Add notifications to your commands
- [Artisan Command](artisan-command) - CLI notification sending
- [Scheduler](scheduler) - Notifications for scheduled tasks
- [Advanced Features](advanced) - Progress bars, attention, hyperlinks
