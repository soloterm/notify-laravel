<?php

namespace SoloTerm\Notify\Laravel\Tests\Feature;

use Orchestra\Testbench\TestCase;
use SoloTerm\Notify\Laravel\NotifyServiceProvider;

class NotifyCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [NotifyServiceProvider::class];
    }

    public function test_command_is_registered(): void
    {
        $this->artisan('list')
            ->assertSuccessful()
            ->expectsOutputToContain('notify');
    }

    public function test_info_option_works(): void
    {
        $this->artisan('notify', ['message' => 'test', '--info' => true])
            ->assertSuccessful();
    }
}
