<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.1
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;
use Illuminate\Support\ServiceProvider;

class BeamzerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred
	 *
	 * @var bool
	 */

	protected $defer = false;


	/**
	 * Bootstrap the application services
	 *
	 * @return void
	 */

	public function boot(Beamzer $beam){

		
		$beamzerConfigs = config('beamzer');

		$beams->settings([
			
			'old_ie_support' => $beamzerConfigs['support_old_ie']

		]);

		if($this->app->runningInConsole()){
			$this->loadConfig();
			$this->loadMigrations();

			/*$this->commands([

			]);*/
		}
	}


	/**
	 * Register the application services
	 *
	 * @return void
	 */

	public function register(){

		$this->mergeConfigFrom(__DIR__.'/../../config/beamzer.php', 'beamzer');
		
		$this->app->singleton('Streamer', function($app){

			return Beamzer::createStream($app->request);
		});

		$this->app->alias('Streamer', 'beamzer');

	}

	/**
	 *
	 */

	private function loadConfig(){

		$configPath = __DIR__.'/../../config/beamzer.php';

    	$this->publishes([
    		$configPath => config_path('beamzer.php')
    	], 'config');
    	
	}

	/**
	 *
	 */

	private function loadMigrations(){

		$migrationsPath = __DIR__.'/../../migrations/';

		$this->publishes([
        	$migrationsPath => database_path('migrations')
    	], 'migrations');
	}
}

?>