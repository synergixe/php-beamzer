# PHPBeamzer

This is a library that adds cross-browser support for real-time feeds and notifications to PHP web applications in an easy way (using Server-Sent Events (SSE) only). It currently supports **Laravel** version 5.4 and 5.5 only. 

## How to Use

```bash

		$ composer require synergixe/php-beamzer


```

```bash

		$ php artisan make:listener NotificableEventListener
		$ php artisan make:controller EventSourceController, MessageController
```

```php

	/* In routes/web.php */

	Route::get('/users/notifications/{id}', 'EventSourceController@getNotifications');


	/* In app/Http/Controllers/EventSourceController.php */

	class EventSourceController extends Controller {

			public function __construct(){

				// code goes here...
			}

			private function pullNotificationStream(Request $request, $user){

				 if(!isset($user)){
				 	return array();
				 }

				 $last_id = $request->input('lastEventId', NULL);
				 
				 if(is_null($last_id)){
				 
				 	return $user->unreadNotifications->take(10)->get();
				 }else{

				 	return $user->unreadNotifications->where('created_at', '>=', intval($last_id))
									->take(10)->get();
				 }

			}

			/*
				The {Streamer} object in injected into the 
				controller method as a dependency.
			*/

			public function getNotifications(Request $request, $id, Streamer $stream){
			    
			    $stream->setup(array(
			    	'as_event' => 'activity'
			    ));
			    
			    return $stream->start(
			        array(
			           'data_source_callback' => array(&$this, 'pullNotificationStream'),
			           'data_source_ops_timeout' => 3000,
			           'data_source_callback_args' => array(
			                    'args' => array(App\User::find($id))
			            )
			        )
			    );
			}

			/* In app/Providers/EventServiceProvider */

			class EventServiceProvider extends ServiceProvider {

				/**
				 * The event listener mappings for the application.
				 *
				 * @var array
				 */
				protected $listen = [
				    'Synergixe\PHPBeamzer\Events\NotificableEvent' => [
				        'App\Listeners\NotificableEventListener',
				    ],
				];
			}

			/* In app/Http/Controllers/MessageController.php */

			class MessageController extends Controller {

				public function __construct(){

					// code goes here...
				}		
				
				public function publishMessage(Request $request) {

					$user = \Auth::user();

					event(new Synergixe\PHPBeamzer\Events\NotificableEvent(
								$user, 
								$user->tasks()->where('active', $request->input('fav'))->get()
					));
				}		
			}

			/* In app/User.php */

			use Synergixe\PHPBemazer\Actionable as Actionable;

			class User extends Eloquent {

				use Notifiable, Actionable, Describable;
				
				public function routeNotificationForMail(){
       
       					return $this->email_address;
    				}
			}
			

			/*In app/Listeners/NotificableEventListener.php */

			class NotificableEventListener implements ShouldQueue {

				use InteractsWithQueue;

				public function __construct(){

					// code goes here...
				}

				public function handle(Synergixe\PHPBeamzer\Events\NotificableEvent $event){

					/* 
						The below code assumes that the {User} model has a 
						relationship called {followers} -> e.g. could be
						followers of a user on a social platform.

						So, all follwers are notified using beamzers'
						custom notification {ActivityStream} with an
						action 'paid'.
					*/

					$user->followers()->get()->each(function($target, $key) use ($event) {
						$target->notify(
				        	new ActivityStream(
				        		$event->producer->setActionPerformed('paid', $event->timing),
				        		$event->payload,
				        		$event->timing
				        	)
					    )->delay(
					        Carbon::now()->addMinutes(5);
					    );
					});
				}

				public function failed(Synergixe\PHPBeamzer\Events\NotificableEvent $event, $exception){

			        	// code goes here...

			    	}
			}

```

## License

MIT

## Requirement

PHP 5.4.0 +

## Support

It isn't absolutely necessary but you can use this library with its front end component library called [beamzer-client](https://github.com/isocroft/beamzer-client/). This front end library support the follwoing browsers:

- IE 8.0+
- FF 4.0+
- Opera 10.5+
- Chrome 3.0+
- Safari 5.0+

## Contributing

You can contribute to this project by setting up a **DOCS** section or sending in a **PR**. report bugs and feature requests to the [issue tracker](https://github.com/synergixe/php-beamzer/issues)    
