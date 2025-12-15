<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Scheduling\Event as ScheduleEvent;
use Illuminate\Support\ServiceProvider;
use SoloTerm\Notify\Laravel\Console\NotifyCommand;
use SoloTerm\Notify\Laravel\Listeners\NotifyOnCommandFinished;
use SoloTerm\Notify\Notify;

class NotifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only register in CLI context
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/notify.php',
            'notify'
        );

        // Register the Notify class as a singleton for the Facade
        $this->app->singleton('notify', function ($app) {
            // Apply config-based settings
            $this->applyConfiguration();

            return new class
            {
                public function __call(string $method, array $args): mixed
                {
                    return Notify::$method(...$args);
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only boot in CLI context
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Apply configuration on boot (in case Facade wasn't used)
        $this->applyConfiguration();

        $this->commands([
            NotifyCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/notify.php' => config_path('notify.php'),
        ], 'notify-config');

        // Register event listeners
        $this->registerEventListeners();

        // Register scheduler macros
        $this->registerSchedulerMacros();
    }

    /**
     * Apply configuration settings to the Notify class.
     */
    protected function applyConfiguration(): void
    {
        // Force protocol if configured
        if ($protocol = config('notify.force_protocol')) {
            Notify::forceProtocol($protocol);
        }

        // Configure fallback
        if (config('notify.enable_fallback', true) === false) {
            Notify::disableFallback();
        }
    }

    /**
     * Register event listeners based on configuration.
     */
    protected function registerEventListeners(): void
    {
        // CommandFinished event listener
        if (config('notify.events.command_finished.enabled', false)) {
            $this->app['events']->listen(
                CommandFinished::class,
                NotifyOnCommandFinished::class
            );
        }
    }

    /**
     * Register scheduler macros.
     */
    protected function registerSchedulerMacros(): void
    {
        if (! class_exists(ScheduleEvent::class)) {
            return;
        }

        // thenNotify - sends notification after task completes
        ScheduleEvent::macro('thenNotify', function (string $message, ?string $title = null) {
            /** @var ScheduleEvent $this */
            return $this->then(function () use ($message, $title) {
                Notify::send(
                    $message,
                    $title ?? config('notify.default_title', 'Laravel')
                );
            });
        });

        // thenNotifySuccess - sends notification only on success
        ScheduleEvent::macro('thenNotifySuccess', function (string $message, ?string $title = null) {
            /** @var ScheduleEvent $this */
            return $this->onSuccess(function () use ($message, $title) {
                Notify::send(
                    $message,
                    $title ?? config('notify.titles.success', 'Success')
                );
            });
        });

        // thenNotifyFailure - sends notification only on failure
        ScheduleEvent::macro('thenNotifyFailure', function (string $message, ?string $title = null) {
            /** @var ScheduleEvent $this */
            return $this->onFailure(function () use ($message, $title) {
                Notify::sendCritical(
                    $message,
                    $title ?? config('notify.titles.error', 'Error')
                );
            });
        });

        // withNotification - convenience method that sets both success and failure handlers
        ScheduleEvent::macro('withNotification', function (
            ?string $successMessage = null,
            ?string $failureMessage = null,
            ?string $title = null
        ) {
            /** @var ScheduleEvent $this */
            $commandName = $this->command ?? $this->description ?? 'Scheduled Task';

            return $this
                ->onSuccess(function () use ($successMessage, $commandName, $title) {
                    $message = $successMessage ?? "{$commandName} completed";
                    Notify::send(
                        $message,
                        $title ?? config('notify.titles.success', 'Success')
                    );
                })
                ->onFailure(function () use ($failureMessage, $commandName, $title) {
                    $message = $failureMessage ?? "{$commandName} failed";
                    Notify::sendCritical(
                        $message,
                        $title ?? config('notify.titles.error', 'Error')
                    );
                });
        });
    }
}
