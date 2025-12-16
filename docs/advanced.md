---
title: Advanced Features
description: Progress bars, attention requests, hyperlinks, and other advanced terminal features.
---

# Advanced Features

Beyond basic notifications, the underlying `soloterm/notify` library provides advanced terminal features accessible through the Facade. These features have varying levels of terminal support.

## Progress Bars

Display progress in the terminal's tab, title bar, or taskbar. This is separate from console progress bars - it shows progress in the terminal chrome itself, visible even when the terminal is minimized or in the background.

### Basic Progress

```php
use SoloTerm\Notify\Laravel\Facades\Notify;

// Show 50% progress
Notify::progress(50);

// Show 100% complete
Notify::progress(100);

// Clear progress indicator
Notify::progressClear();
```

### Progress States

Different visual states for the progress bar:

```php
// Normal progress (blue/default)
Notify::progress(75);
Notify::progress(75, Notify::PROGRESS_NORMAL);

// Error state (red) - indicates failure
Notify::progressError(100);
Notify::progress(100, Notify::PROGRESS_ERROR);

// Paused state (yellow) - indicates waiting/paused
Notify::progressPaused(50);
Notify::progress(50, Notify::PROGRESS_PAUSED);

// Indeterminate (spinning/pulsing) - unknown duration
Notify::progressIndeterminate();
Notify::progress(0, Notify::PROGRESS_INDETERMINATE);

// Hidden (clear the progress bar)
Notify::progressClear();
Notify::progress(0, Notify::PROGRESS_HIDDEN);
```

### Progress Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `PROGRESS_HIDDEN` | 0 | Hide progress indicator |
| `PROGRESS_NORMAL` | 1 | Normal progress (default, typically blue) |
| `PROGRESS_ERROR` | 2 | Error state (typically red) |
| `PROGRESS_INDETERMINATE` | 3 | Unknown progress (spinning/pulsing) |
| `PROGRESS_PAUSED` | 4 | Paused state (typically yellow) |

### Command with Progress Bar

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoloTerm\Notify\Laravel\Facades\Notify;

class ProcessRecords extends Command
{
    protected $signature = 'records:process';
    protected $description = 'Process all pending records';

    public function handle(): int
    {
        $records = $this->getPendingRecords();
        $total = count($records);

        // Show indeterminate progress while loading
        if (Notify::supportsProgress()) {
            Notify::progressIndeterminate();
        }

        foreach ($records as $index => $record) {
            $percent = (int) (($index + 1) / $total * 100);

            // Update tab/taskbar progress
            Notify::progress($percent);

            // Also show console progress
            $this->output->write("\rProcessing: {$percent}%");

            $this->processRecord($record);
        }

        $this->newLine();

        // Clear progress and notify completion
        Notify::progressClear();
        Notify::success("Processed {$total} records");

        return self::SUCCESS;
    }
}
```

### Progress with Error Handling

```php
public function handle(): int
{
    try {
        $this->runMigrations();
        Notify::progressClear();
        Notify::success('Migrations complete');
        return self::SUCCESS;
    } catch (\Exception $e) {
        // Show error state in progress bar
        Notify::progressError(100);
        Notify::error('Migration failed: ' . $e->getMessage());
        return self::FAILURE;
    }
}
```

### Terminal Support for Progress

Progress bars use the OSC 9;4 escape sequence. Supported terminals:

| Terminal | Support | Notes |
|----------|---------|-------|
| **Windows Terminal** | Full | Native Windows taskbar integration |
| **Ghostty** | Full | Version 1.2+ |
| **iTerm2** | Full | Version 3.6.6+ |
| **ConEmu** | Full | Windows |
| **Mintty** | Full | Windows |
| Kitty | No | Not supported |
| WezTerm | No | Not supported |
| Alacritty | No | Not supported |

### Always Check Support

Progress methods return `false` silently on unsupported terminals:

```php
// Safe to call without checking - just returns false if unsupported
Notify::progress(50);

// But you may want to skip entirely for performance
if (Notify::supportsProgress()) {
    foreach ($items as $i => $item) {
        Notify::progress(($i + 1) / count($items) * 100);
        $this->process($item);
    }
    Notify::progressClear();
}
```

## Attention Requests

Request user attention when the terminal window is in the background.

### Basic Attention

```php
// Request attention (bounces dock icon on macOS)
Notify::requestAttention();

// Request attention with fireworks animation (iTerm2)
Notify::requestAttention(true);
// or
Notify::fireworks();
```

### Steal Focus

Bring the terminal window to the foreground:

```php
Notify::stealFocus();
```

### Terminal Support for Attention

| Method | iTerm2 | Other Terminals |
|--------|--------|-----------------|
| `requestAttention()` | Dock bounce | Varies |
| `fireworks()` | Visual effect | No effect |
| `stealFocus()` | Focus window | Varies |

### Attention Example

```php
public function handle(): int
{
    $this->info('Starting long operation...');

    // ... long operation ...

    // Alert user when done
    Notify::requestAttention();
    Notify::success('Operation complete!');

    return self::SUCCESS;
}
```

## Hyperlinks

Create clickable hyperlinks in terminal output using OSC 8.

### Basic Hyperlinks

```php
// Create a hyperlink
$link = Notify::hyperlink('https://example.com', 'Click here');
$this->line("Visit our site: {$link}");

// URL as display text
$link = Notify::hyperlink('https://example.com');
// Displays: https://example.com (clickable)

// With ID for grouping related links
$link = Notify::hyperlink('https://example.com', 'Link', 'link-1');
```

### Hyperlink Example

```php
public function handle(): int
{
    $report = $this->generateReport();
    $url = route('reports.show', $report);

    $link = Notify::hyperlink($url, 'View Report');
    $this->info("Report generated: {$link}");

    return self::SUCCESS;
}
```

### Terminal Support for Hyperlinks

Most modern terminals support OSC 8 hyperlinks:

| Terminal | Support |
|----------|---------|
| iTerm2 | Full |
| Kitty | Full |
| WezTerm | Full |
| Ghostty | Full |
| GNOME Terminal | Full |
| Windows Terminal | Full |
| Alacritty | No |

## Notification IDs and Closing

With OSC 99 (Kitty), you can assign IDs to notifications and close them programmatically.

### Notification with ID

```php
// Send notification with ID
Notify::send('Processing...', 'Status', null, 'my-notification');

// Later, close it
Notify::close('my-notification');
```

### Replace/Update Pattern

```php
// Initial notification
Notify::send('Starting...', 'Build', null, 'build-status');

// Update by sending with same ID
Notify::send('50% complete...', 'Build', null, 'build-status');

// Final update
Notify::send('Build complete!', 'Build', null, 'build-status');
```

### Terminal Support for Notification IDs

Only Kitty (OSC 99) supports notification IDs and closing. Other terminals will ignore the ID parameter.

## Multiplexer Detection

Detect if running inside tmux or GNU Screen:

```php
if (Notify::inTmux()) {
    $this->warn('Running inside tmux');
    $this->line('Ensure "allow-passthrough on" is set in ~/.tmux.conf');
}

if (Notify::inScreen()) {
    $this->warn('Running inside GNU Screen');
}
```

## Capability Detection

Check what features are available:

```php
// Full capabilities array
$caps = Notify::capabilities();
/*
[
    'can_notify' => true,
    'protocol' => 'osc99',
    'terminal' => 'kitty',
    'in_tmux' => false,
    'in_screen' => false,
    'supports_progress' => true,
    ...
]
*/

// Individual checks
$canNotify = Notify::canNotify();
$canFallback = Notify::canFallback();
$supportsProgress = Notify::supportsProgress();
$terminal = Notify::getTerminal();  // 'kitty', 'iterm2', etc.
$protocol = Notify::getProtocol();  // 'osc99', 'osc9', 'osc777'
```

## Feature Support Matrix

| Feature | OSC 9 (iTerm2) | OSC 777 (WezTerm/Ghostty) | OSC 99 (Kitty) |
|---------|----------------|---------------------------|----------------|
| Basic notification | Message only | Title + message | Full |
| Title | No | Yes | Yes |
| Urgency levels | No | No | Yes |
| Notification ID | No | No | Yes |
| Close notification | No | No | Yes |

| Feature | Support |
|---------|---------|
| Progress bars | Windows Terminal, iTerm2 3.6.6+, Ghostty |
| Hyperlinks | Most modern terminals |
| Attention/Focus | iTerm2 best, others vary |

## Best Practices

### Check Before Using Advanced Features

```php
// Progress bars
if (Notify::supportsProgress()) {
    Notify::progress(50);
}

// Always safe - graceful degradation
Notify::send('Message');  // Falls back appropriately
```

### Use IDs Sparingly

Notification IDs only work in Kitty. For cross-terminal compatibility, design your notifications to work without IDs.

### Progress for Long Operations

```php
public function handle(): int
{
    if (Notify::supportsProgress()) {
        $this->useProgressIndicator();
    } else {
        $this->useConsoleProgressBar();
    }

    return self::SUCCESS;
}
```

## Next Steps

- [Facade](facade) - Core notification methods
- [Configuration](configuration) - Protocol and fallback settings
