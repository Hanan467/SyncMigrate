<?php

namespace Hanan467\SyncMigrate;

use Illuminate\Support\ServiceProvider;
use Hanan467\SyncMigrate\Commands\RunSyncMigrate;

class SyncMigrateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            RunSyncMigrate::class,
        ]);
    }

    public function boot()
    {
        //
    }
}
