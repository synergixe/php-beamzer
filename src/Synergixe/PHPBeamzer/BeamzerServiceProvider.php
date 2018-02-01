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

		if($this->app->runningInConsole()){
			$this->loadConfig();
			// $this->loadMigrations();

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
			
			/*
				if($app->request->hasHeader('Last-Event-ID')){
				    $last_event_id = $app->request->header('Last-Event-ID', 0);
				}
			*/
		
			$last_event_id = $app->request->query->('lastEventId');
			
			if(!isset($last_event_id)){
				 $last_event_id = $app->request->headers->get('LAST_EVENT_ID');
			}
			
			$app->request->query->add(['lastEventId' => $last_event_id]);

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
