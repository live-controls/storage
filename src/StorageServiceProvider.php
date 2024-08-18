<?php

namespace LiveControls\Storage;

use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
  public function register()
  { 
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'livecontrols_storage');
  }

  public function boot()
  {
    $this->publishes([
      __DIR__.'/../config/config.php' => config_path('livecontrols_storage.php'),
    ], 'livecontrols.storage.config');

    if ($this->app->runningInConsole()) {
      if (! class_exists('CreateDbDiskTable')) {
        $this->publishes([
          __DIR__ . '/../database/migrations/create_dbdisk_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_dbdisk_table.php'),
          // you can add any number of migrations here
        ], 'migrations');
      }
    }
  }
}
