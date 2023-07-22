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
  }
}
