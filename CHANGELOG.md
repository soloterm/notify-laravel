# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2025-12-16

### Added
- `php artisan notify` command for sending desktop notifications
  - `--title` option for notification titles
  - `--protocol` option to force OSC protocol (osc9, osc777, osc99)
  - `--exit-code` flag for automatic success/failure notifications
  - `--info` flag to show terminal detection info
- `php artisan notify:diagnose` command for interactive terminal capability testing
- `Notify` Facade with semantic methods (`success()`, `error()`, `warning()`, `info()`)
- `SendsNotifications` trait for adding notifications to custom Artisan commands
- Scheduler macros for task completion notifications
  - `thenNotify()` - notify after task completes
  - `thenNotifySuccess()` - notify only on success
  - `thenNotifyFailure()` - notify only on failure
  - `withNotification()` - notify on both success and failure
- Event listener for automatic notifications when Artisan commands finish
- Notify log channel for routing log messages to desktop notifications
- Progress bar support via Facade (`progress()`, `progressClear()`, `progressError()`, `progressPaused()`, `progressIndeterminate()`)
- Publishable configuration file with customizable titles and behavior
- Support for Laravel 10, 11, and 12


[Unreleased]: https://github.com/soloterm/notify-laravel/commits/HEAD
[0.1.0]: https://github.com/soloterm/notify-laravel/releases/tag/v0.1.0
