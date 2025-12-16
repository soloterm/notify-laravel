<?php

declare(strict_types=1);

namespace SoloTerm\Notify\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use SoloTerm\Notify\Notify as BaseNotify;

/**
 * Facade for the Notify library.
 *
 * @method static bool send(string $message, ?string $title = null, ?int $urgency = null, ?string $id = null)
 * @method static bool sendLow(string $message, ?string $title = null)
 * @method static bool sendCritical(string $message, ?string $title = null)
 * @method static bool bell()
 * @method static bool sendOrBell(string $message, ?string $title = null)
 * @method static bool sendAny(string $message, ?string $title = null, ?int $urgency = null)
 * @method static bool sendExternal(string $message, ?string $title = null, ?int $urgency = null)
 * @method static bool close(string $id)
 * @method static bool canNotify()
 * @method static bool canFallback()
 * @method static bool supportsProgress()
 * @method static ?string getTerminal()
 * @method static ?string getProtocol()
 * @method static array capabilities()
 * @method static void forceProtocol(?string $protocol)
 * @method static void enableFallback(bool $enabled = true)
 * @method static void disableFallback()
 * @method static void setDefaultUrgency(int $urgency)
 * @method static void reset()
 * @method static bool progress(int $progress, int $state = 1)
 * @method static bool progressClear()
 * @method static bool progressError(int $progress = 100)
 * @method static bool progressPaused(int $progress)
 * @method static bool progressIndeterminate()
 * @method static bool requestAttention(bool $fireworks = false)
 * @method static bool fireworks()
 * @method static bool stealFocus()
 * @method static string hyperlink(string $url, ?string $text = null, ?string $id = null)
 *
 * @see \SoloTerm\Notify\Notify
 */
class Notify extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'notify';
    }

    /**
     * Send a success notification.
     */
    public static function success(string $message, ?string $title = null): bool
    {
        return BaseNotify::send(
            $message,
            $title ?? config('notify.titles.success', 'Success')
        );
    }

    /**
     * Send an error notification with critical urgency.
     */
    public static function error(string $message, ?string $title = null): bool
    {
        return BaseNotify::sendCritical(
            $message,
            $title ?? config('notify.titles.error', 'Error')
        );
    }

    /**
     * Send a warning notification.
     */
    public static function warning(string $message, ?string $title = null): bool
    {
        return BaseNotify::send(
            $message,
            $title ?? config('notify.titles.warning', 'Warning')
        );
    }

    /**
     * Send an info notification with low urgency.
     */
    public static function info(string $message, ?string $title = null): bool
    {
        return BaseNotify::sendLow(
            $message,
            $title ?? config('notify.titles.info', 'Info')
        );
    }
}
