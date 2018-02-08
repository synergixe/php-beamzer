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

namespace Synergixe\PHPBeamzer\Providers\Laravel;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;
use Synergixe\PHPBeamzer\Facades\Streamer as Streamer;
use Synergixe\PHPBeamzer\Commands\ComposeRedisServiceCommand as ComposeRedisServiceCommand;
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

	public function boot(){
		
		$this->loadConfig();
		
		// $this->mergeConfigFrom(__DIR__.'/../../config/beamzer.php', 'beamzer');

		/*if($this->app->runningInConsole()){
			
			$this->commands([
				ComposeRedisServiceCommand::class
			]);
		}*/
	}


	/**
	 * Register the application services
	 *
	 * @return void
	 */

	public function register(){

		
		$this->app->singleton('beamzer', function($app){
			
			/*
				Setup the Last-Event-Id value for the package
			*/
		
			$last_event_id = $app->request->query->get('lastEventId');
			
			/*if(!isset($last_event_id)){
				 $last_event_id = $app->request->headers->get('LAST_EVENT_ID');
			}*/
			
			if(!isset($last_event_id) 
			   	&& $app->request->hasHeader('Last-Event-ID')){
				    $last_event_id = $app->request->header('Last-Event-ID', 0);
			}else{
			
				$last_event_id = NULL;
			}
			
			$app->request->query->add(['lastEventId' => $last_event_id]);
			
			$redis_config = $app->make('config')->get('database.redis');
			
			$redis = NULL;
			
			if((array_key_exists('client', $redis_config)) 
				&& ($redis_config['client'] === 'predis')){
				
				$redis = $app['redis']->connection();
			}

			return Beamzer::createInstance($app->request, $redis);
		});

		$this->app->alias('beamzer', Streamer::class);

	}
	
	/**
	     * Get the services provided by the provider.
	     *
	     * @return array
	     */
	
	    public function provides()
	    {
		return ['beamzer', Streamer::class];
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
