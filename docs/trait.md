---
title: SendsNotifications Trait
description: Add notification capabilities to your Artisan commands.
---

# SendsNotifications Trait

The `SendsNotifications` trait adds notification methods directly to your Artisan commands, making it easy to notify users when commands complete or encounter issues.

## Basic Usage

Add the trait to any Artisan command:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoloTerm\Notify\Laravel\Concerns\SendsNotifications;

class BuildProject extends Command
{
    use SendsNotifications;

    protected $signature = 'build:project';
    protected $description = 'Build the project';

    public function handle(): int
    {
        $this->info('Building project...');

        // ... build logic ...

        $this->notifySuccess('Build completed!');

        return self::SUCCESS;
    }
}
```

## Available Methods

### notify()

Send a basic notification:

```php
$this->notify('Message');
$this->notify('Message', 'Custom Title');
```

Returns `true` if the notification was sent, `false` if notifications aren't supported.

### notifySuccess()

Send a success notification with normal urgency:

```php
$this->notifySuccess('Task completed');
$this->notifySuccess('All tests passed', 'Tests');
```

Default title: Uses config value at `notify.titles.success` (default: "Success")

### notifyError()

Send an error notification with critical urgency:

```php
$this->notifyError('Build failed');
$this->notifyError('Connection lost', 'Database');
```

Default title: Uses config value at `notify.titles.error` (default: "Error")

Critical urgency may bypass Do Not Disturb on some systems.

### notifyWarning()

Send a warning notification with normal urgency:

```php
$this->notifyWarning('Low disk space');
$this->notifyWarning('High memory usage', 'Resources');
```

Default title: Uses config value at `notify.titles.warning` (default: "Warning")

### canNotify()

Check if the terminal supports notifications:

```php
if ($this->canNotify()) {
    $this->notify('Terminal supports notifications!');
} else {
    $this->warn('Notifications not supported in this terminal');
}
```

## Complete Example

Here's a comprehensive example showing the trait in action:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoloTerm\Notify\Laravel\Concerns\SendsNotifications;

class ProcessData extends Command
{
    use SendsNotifications;

    protected $signature = 'data:process {--force}';
    protected $description = 'Process pending data records';

    public function handle(): int
    {
        $this->info('Starting data processing...');

        try {
            $count = $this->processRecords();

            if ($count === 0) {
                $this->notifyWarning('No records to process');
                return self::SUCCESS;
            }

            $this->notifySuccess("Processed {$count} records");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->notifyError('Processing failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function processRecords(): int
    {
        // Processing logic...
        return 42;
    }
}
```

## Graceful Degradation

The trait methods gracefully handle unsupported terminals:

```php
// These won't throw errors even if notifications aren't supported
$this->notify('Message');        // Returns false silently
$this->notifySuccess('Done');    // Returns false silently
$this->notifyError('Failed');    // Returns false silently
```

This means you can use notifications without checking `canNotify()` first - they'll simply be skipped when not available.

## When to Use the Trait vs Facade

| Scenario | Use |
|----------|-----|
| Inside an Artisan command | `SendsNotifications` trait |
| Inside a job, service, or other class | `Notify` Facade |
| One-off notification from anywhere | `Notify` Facade |
| Multiple notifications throughout a command | `SendsNotifications` trait |

The trait is more ergonomic for commands since you can use `$this->notify()` instead of importing and using the Facade.

## Combining with Other Features

### With Progress Output

```php
public function handle(): int
{
    $items = $this->getItems();
    $bar = $this->output->createProgressBar(count($items));

    foreach ($items as $item) {
        $this->processItem($item);
        $bar->advance();
    }

    $bar->finish();
    $this->newLine();

    $this->notifySuccess('Processing complete');
    return self::SUCCESS;
}
```

### With Verbose Output

```php
public function handle(): int
{
    // ... processing ...

    if ($this->option('verbose')) {
        $this->line('Sending notification...');
    }

    $sent = $this->notifySuccess('Done');

    if ($this->option('verbose') && !$sent) {
        $this->warn('Notification could not be sent');
    }

    return self::SUCCESS;
}
```

### With Confirmation

```php
public function handle(): int
{
    if (!$this->confirm('This will process all records. Continue?')) {
        return self::SUCCESS;
    }

    // ... processing ...

    $this->notifySuccess('Processing complete');
    return self::SUCCESS;
}
```

## Default Titles

The trait uses configured default titles from `config/notify.php`:

| Method | Config Key | Default |
|--------|-----------|---------|
| `notifySuccess()` | `notify.titles.success` | "Success" |
| `notifyError()` | `notify.titles.error` | "Error" |
| `notifyWarning()` | `notify.titles.warning` | "Warning" |
| `notify()` | `notify.default_title` | "Laravel" |

Override in your command by passing a custom title:

```php
$this->notifySuccess('Done', 'My Custom Title');
```

## Method Reference

| Method | Urgency | Returns | Description |
|--------|---------|---------|-------------|
| `notify($message, $title?)` | Normal | bool | Basic notification |
| `notifySuccess($message, $title?)` | Normal | bool | Success notification |
| `notifyError($message, $title?)` | Critical | bool | Error notification |
| `notifyWarning($message, $title?)` | Normal | bool | Warning notification |
| `canNotify()` | - | bool | Check notification support |

## Next Steps

- [Facade](facade) - Use notifications outside commands
- [Scheduler](scheduler) - Notify on scheduled task completion
- [Artisan Command](artisan-command) - CLI notification sending
