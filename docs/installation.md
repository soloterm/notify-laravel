---
title: Installation
description: Install and configure Notify for Laravel.
---

# Installation

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| Laravel | 10, 11, or 12 |
| Terminal | See [terminal support](#terminal-support) |

## Install via Composer

Install as a development dependency (recommended):

```bash
composer require soloterm/notify-laravel --dev
```

Or as a regular dependency if you need notifications in production:

```bash
composer require soloterm/notify-laravel
```

The service provider is auto-discovered by Laravel.

## Verify Installation

Check if your terminal supports notifications:

```bash
php artisan notify --info
```

Expected output:

```
Terminal ......................................... iTerm2
Protocol ......................................... osc9
Can Notify ....................................... Yes
In tmux .......................................... No
In Screen ........................................ No
```

Send a test notification:

```bash
php artisan notify "Hello from Laravel!"
```

## Configuration

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=notify-config
```

This creates `config/notify.php` with options for default titles, protocol forcing, fallback behavior, and event listener settings.

See the [Configuration](configuration) page for full documentation of all options.

## Terminal Support

### Supported Terminals

These terminals support OSC notifications natively:

- **iTerm2** (macOS) - OSC 9
- **Kitty** (Cross-platform) - OSC 99 with full features
- **WezTerm** (Cross-platform) - OSC 777
- **Ghostty** (Cross-platform) - OSC 777
- **Foot** (Linux/Wayland) - OSC 777

### Fallback Terminals

These terminals use system notification tools:

- **Alacritty** - Uses `notify-send` (Linux) or `osascript` (macOS)
- **Terminal.app** - Uses `osascript` (macOS)
- **Windows Terminal** - Uses PowerShell notifications

### tmux Configuration

For notifications inside tmux, add to `~/.tmux.conf`:

```bash
set -g allow-passthrough on
```

Reload the configuration:

```bash
tmux source-file ~/.tmux.conf
```

### GNU Screen Configuration

For notifications inside GNU Screen, add to `~/.screenrc`:

```bash
maptimeout 5
defobuflimit 0
```

## Troubleshooting

### "Desktop notifications are not supported"

1. Check terminal detection:
   ```bash
   php artisan notify --info
   ```

2. If using tmux, ensure passthrough is enabled:
   ```bash
   tmux show -g allow-passthrough
   # Should show: allow-passthrough on
   ```

3. Try forcing a protocol:
   ```bash
   php artisan notify "Test" --protocol=osc777
   ```

### Notifications not appearing

1. Check system notification settings (Do Not Disturb, Focus mode)
2. Verify terminal has notification permissions in system preferences
3. Check if fallback is enabled in config

## Next Steps

- [Configuration](configuration) - Full configuration options
- [Facade](facade) - Use the Notify Facade in your code
- [Artisan Command](artisan-command) - Send notifications from CLI
