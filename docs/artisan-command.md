---
title: Artisan Command
description: Send notifications from the command line.
---

# Artisan Command

The `php artisan notify` command sends desktop notifications from the command line. Perfect for:

- Alerting when commands finish
- Piping results from other tools
- Quick testing of notification support

## Basic Usage

```bash
# Simple message
php artisan notify "Build complete!"

# Message with title
php artisan notify "All tests passed" --title="Tests"
php artisan notify "All tests passed" -t "Tests"
```

## Exit Code Mode

The most powerful feature: automatically notify based on the result of the previous command.

```bash
# Run tests, then notify based on result
php artisan test; php artisan notify --exit-code=$?
php artisan test; php artisan notify -e $?

# Works with any command
npm run build; php artisan notify -e $?
./vendor/bin/phpunit; php artisan notify -e $?
composer install; php artisan notify -e $?
```

The notification shows:
- **Exit code 0**: Success notification with checkmark
- **Non-zero**: Failure notification with the exit code

### Custom Messages

Override the default success/failure messages:

```bash
# Custom success message
php artisan migrate; php artisan notify -e $? \
    --success-message="Migrations applied"

# Custom failure message
php artisan migrate; php artisan notify -e $? \
    --failure-message="Migration failed - check logs"

# Both messages
php artisan test; php artisan notify -e $? \
    --success-message="All tests passed" \
    --failure-message="Tests failed"
```

### Custom Titles

Override the default success/failure titles:

```bash
php artisan test; php artisan notify -e $? \
    --success-title="PHPUnit" \
    --failure-title="PHPUnit Failed"
```

### Full Example

```bash
php artisan migrate; php artisan notify -e $? \
    --success-message="Database updated" \
    --failure-message="Migration error" \
    --success-title="Migrations" \
    --failure-title="Migrations"
```

## Protocol Override

Force a specific OSC protocol:

```bash
# Force OSC 777 (WezTerm, Ghostty)
php artisan notify "Hello" --protocol=osc777
php artisan notify "Hello" -p osc777

# Force OSC 9 (iTerm2)
php artisan notify "Hello" -p osc9

# Force OSC 99 (Kitty)
php artisan notify "Hello" -p osc99
```

## Terminal Info

Check your terminal's notification capabilities:

```bash
php artisan notify --info
```

Output:

```
Terminal ......................................... Kitty
Protocol ......................................... osc99
Can Notify ....................................... Yes
In tmux .......................................... No
In Screen ........................................ No
```

If using tmux without passthrough enabled:

```
Terminal ......................................... Unknown
Protocol ......................................... None
Can Notify ....................................... No
In tmux .......................................... Yes
In Screen ........................................ No

  WARN  tmux detected. Ensure "allow-passthrough on" is set in ~/.tmux.conf
```

## Command Reference

```
php artisan notify [message] [options]

Arguments:
  message                    The notification message

Options:
  -t, --title=TITLE          Notification title
  -p, --protocol=PROTOCOL    Force protocol (osc9, osc777, osc99)
  -e, --exit-code=CODE       Exit code from previous command
  --success-message=MSG      Message for success (exit code 0)
  --failure-message=MSG      Message for failure (non-zero)
  --success-title=TITLE      Title for success notifications
  --failure-title=TITLE      Title for failure notifications
  --info                     Show terminal detection info
```

## Shell Integration

### Bash/Zsh Alias

Add to your `~/.bashrc` or `~/.zshrc`:

```bash
# Quick notify after any command
alias n='php artisan notify -e $?'

# Usage: php artisan test; n
```

### Function with Custom Title

```bash
notify_done() {
    local exit_code=$?
    php artisan notify -e $exit_code \
        --success-title="${1:-Done}" \
        --failure-title="${1:-Failed}"
}

# Usage: php artisan migrate; notify_done "Migrations"
```

### Watch Long Commands

```bash
# Notify when a long command finishes
php artisan db:seed --class=LargeDataSeeder; php artisan notify -e $? \
    --success-message="Seeding complete"
```

## Practical Examples

### After Tests

```bash
# Simple
php artisan test; php artisan notify -e $?

# With custom messages
php artisan test; php artisan notify -e $? \
    -t "Tests" \
    --success-message="All tests passed" \
    --failure-message="Test suite failed"
```

### After Builds

```bash
npm run build; php artisan notify -e $? \
    -t "Build" \
    --success-message="Production build ready" \
    --failure-message="Build failed - check errors"
```

### After Deployments

```bash
php artisan deploy; php artisan notify -e $? \
    --success-title="Deployed" \
    --failure-title="Deploy Failed" \
    --success-message="Production updated" \
    --failure-message="Deployment error - check logs"
```

### Multiple Commands

```bash
# Run multiple commands, notify on final result
(php artisan migrate && php artisan db:seed); php artisan notify -e $? \
    --success-message="Database ready"
```

## Error Handling

If notifications aren't supported, the command shows an error:

```
  ERROR  Desktop notifications are not supported in this terminal.

Terminal ......................................... Unknown
Protocol ......................................... None
Can Notify ....................................... No
```

The command returns exit code 1 in this case, so you can handle it:

```bash
php artisan notify "Test" || echo "Notifications not available"
```

## Next Steps

- [Scheduler](scheduler) - Automatic notifications for scheduled tasks
- [Facade](facade) - Use notifications in your PHP code
