<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Title
    |--------------------------------------------------------------------------
    |
    | The default title used for notifications when no title is specified.
    |
    */

    'default_title' => env('NOTIFY_DEFAULT_TITLE', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Semantic Titles
    |--------------------------------------------------------------------------
    |
    | Default titles for semantic notification methods (success, error, etc.).
    |
    */

    'titles' => [
        'success' => env('NOTIFY_TITLE_SUCCESS', 'Success'),
        'error' => env('NOTIFY_TITLE_ERROR', 'Error'),
        'warning' => env('NOTIFY_TITLE_WARNING', 'Warning'),
        'info' => env('NOTIFY_TITLE_INFO', 'Info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Force Protocol
    |--------------------------------------------------------------------------
    |
    | Force a specific OSC protocol instead of auto-detecting. Leave null
    | for automatic detection. Valid options: 'osc9', 'osc777', 'osc99'
    |
    */

    'force_protocol' => env('NOTIFY_FORCE_PROTOCOL'),

    /*
    |--------------------------------------------------------------------------
    | External Fallback
    |--------------------------------------------------------------------------
    |
    | When enabled, notifications will fall back to system notification tools
    | (notify-send on Linux, osascript on macOS, PowerShell on Windows) when
    | OSC notifications are not supported by the terminal.
    |
    */

    'enable_fallback' => env('NOTIFY_ENABLE_FALLBACK', true),

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    |
    | Configure automatic notifications for Laravel events.
    |
    */

    'events' => [

        /*
        | CommandFinished Event
        |
        | When enabled, sends a notification when Artisan commands complete.
        */
        'command_finished' => [
            'enabled' => env('NOTIFY_ON_COMMAND_FINISHED', false),
            'success_title' => 'Command Completed',
            'failure_title' => 'Command Failed',
        ],

        /*
        | Excluded Commands
        |
        | Commands that should never trigger notifications.
        */
        'excluded_commands' => [
            'notify',
            'list',
            'help',
            'env',
            'schedule:run',
            'queue:work',
            'queue:listen',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the notify log channel. To use, add this to your
    | config/logging.php channels array:
    |
    | 'notify' => [
    |     'driver' => 'custom',
    |     'via' => \SoloTerm\Notify\Laravel\Logging\CreateNotifyLogger::class,
    |     'level' => 'warning',
    |     'title' => 'My App',
    | ],
    |
    */

    'logging' => [
        'default_level' => 'warning',
        'max_length' => 200,
    ],

];
