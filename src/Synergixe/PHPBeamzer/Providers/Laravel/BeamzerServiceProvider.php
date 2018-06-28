<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.6
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT
 *
 */

namespace Synergixe\PHPBeamzer\Providers\Laravel;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;
use Synergixe\PHPBeamzer\Facades\Laravel\Streamer as Streamer;
use Synergixe\PHPBeamzer\Commands\ComposeRedisServiceCommand as ComposeRedisServiceCommand;
use Illuminate\Support\ServiceProvider;

class BeamzerServiceProvider extends ServiceProvider {
	
	const VERSION = '0.1.6';

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

		if($this->app->runningInConsole()){
		
			$this->loadConfig();
			
		}
	}


	/**
	 * Register the application services
	 *
	 * @return void
	 */

	public function register(){
		
		$this->mergeConfig();

		$this->app->singleton(Streamer::class, function($app){

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
				&& ($redis_config['client'] === 'predis')
					&& class_exists('Predis\Client')){

						$redis = $app['redis']->connection();
			}

			return Beamzer::createInstance($app->request, $redis);
		});

		$this->app->alias(Streamer::class, Beamzer::class);
		
		$this->registerCommands();

	}
	
	protected function registerCommands(){
	
		$this->commands([
			ComposeRedisServiceCommand::class,
			LaravelNotificationLoadCommand::class
		]);
	}

	/**
	     * Get the services provided by the provider.
	     *
	     * @return array
	     */

	    public function provides()
	    {
		return [Streamer::class];
	    }
	
	/**
     	* Merge configurations.
     	*/
    	
	protected function mergeConfig()
    	{
		$app = $this->app;
		
		/*if($app instanceof LaravelApplication
		  	&& $app->runningInConsole()){
			;
		}else if($app instanceof LumenApplication){
			$app->configure('beamzer');
		}*/
		
		$this->mergeConfigFrom(__DIR__.'/../../../../../config/beamzer.php', 'beamzer');
    	}

	/**
	 * Load Configurations
	 *
	 */

	private function loadConfig(){

		$configPath = __DIR__.'/../../../../../config/beamzer.php';

		$this->publishes([
			$configPath => config_path('beamzer.php')
		], 'config');

	}

	/**
	 *
	 *
	 */

	private function loadMigrations(){

		$migrationsPath = __DIR__.'/../../../../../migrations/';

		$this->publishes([
			$migrationsPath => database_path('migrations')
		], 'migrations');
	}
}

?>
