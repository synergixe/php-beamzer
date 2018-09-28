<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.7
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT
 *
 */

namespace Synergixe\PHPBeamzer\Providers\Laravel;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;
use Synergixe\PHPBeamzer\Facades\Laravel\Streamer as Streamer;
use Synergixe\PHPBeamzer\Commands\ComposeRedisServiceCommand as ComposeRedisServiceCommand;
use Illuminate\Support\ServiceProvider;

use ReflectionClass;

class BeamzerServiceProvider extends ServiceProvider {
	
	const VERSION = '0.1.7';

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

		$this->setupConfig();
	}


	/**
	 * Register the application services
	 *
	 * @return void
	 */

	public function register(){

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
     	* Setup configurations.
     	*/
    	
	protected function setupConfig()
    	{
		$app = $this->app;
		
		$src_path = realpath($raw = __DIR__.'/../../../../../config/beamzer.php') ?: $raw;
		
		if($app instanceof LaravelApplication
		  	&& $app->runningInConsole()){
			$this->loadConfig($src_path);
		}else if($app instanceof LumenApplication){
			$app->configure('beamzer');
		}
		
		$this->mergeConfigFrom($src_path, 'beamzer');
    	}

	/**
	 * Load Configurations
	 *
	 */

	private function loadConfig($configPath){

		$this->publishes([
			$configPath => config_path('beamzer.php')
		], 'config');

	}

	/**
	 *
	 * Load Migrations
	 */

	private function loadMigrations(){

		$migrationsPath = __DIR__.'/../../../../../migrations/';

		$this->publishes([
			$migrationsPath => database_path('migrations')
		], 'migrations');
	}
}

?>
