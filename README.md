# PHPBeamzer

This is a library that adds cross-browser support for real-time feeds and notifications to PHP web applications in an easy way (using Server-Sent Events (SSE) only). It currently supports **Laravel** version 5.4 and 5.5 only (for now). 

## How to Use

### Load Up the package from Composer

```bash

		$ composer require synergixe/php-beamzer


```

### Then create your Controllers

```bash

		$ php artisan make:controller EventSourceController
		
		$ php artisan make:controller MessageController
```

### Register the route for returning your stream notifications and for create notifications

```php

	/* In routes/web.php */

	Route::get('/users/notifications', 'EventSourceController@getNotifications');
	Route::post('/notify/subjects/{kind}', 'MessageController@fireNotificationEvent');
	Route::patch('/user/notifications/update/{nid}', 'EventSourceController@updateNotificationsAsRead');
	
```

### Update the app config for service provider and alias classes

```php

	/* In app/config/app.php */

	'providers' => [
	    ...
	    Synergixe\PHPBeamzer\Providers\Laravel\BeamzerServiceProvider::class
	],
	'alias' => [
	    ...
	    'Streamer' => Synergixe\PHPBeamzer\Facades\Laravel\Streamer::class
	]

```

### Setup the Controller for read notifications from the DB and return it to PHPBeamzer

```php


	/* In app/Http/Controllers/EventSourceController.php */

	class EventSourceController extends Controller {

			public function __construct(){

				// code goes here...
			}

			private function pullNotificationData(Request $request, $user){

				 if(!isset($user)){
				 	return array();
				 }

				 $last_id = $request->input('lastEventId', NULL);
				 
				 if(is_null($last_id)){
				 
				 	return $user->unreadNotifications->take(10)->get();
				 }else{

				 	return $user->unreadNotifications->where('created_at', '>', $last_id)
									->take(10)->get();
				 }

			}
			
			public function deleteAllUnreadNotifications(Request $request){
			
				$user = \Auth::user();
			
				$user->unreadNotifications()->delete();
				
				return response()->json(array('status' => 'ok'));
			}
			
			public function countAllUnreadNotifications(Request $request){
			
				$user = \Auth::user();
			
				$count = $user->unreadNotifications()->groupBy('notifiable_type')->count();
				
				return response()->json(array('count' => $count));
			}
			
			/*
				The $nid variable is the notification id sent via 
				AJAX (as a PATCH request) to update the status of
				the notification to "read"
			*/
			
			public function updateNotificationsAsRead(Request $request, $nid){
				
					$user = \Auth::user();
					
					$user->unReadNotifications()
						->where('id', $nid)
							->update(['read_at' => date('Y-m-d H:i:s')]);
							
					if($request->expectsJson())
						return response()->json(array('status' => 'ok'));
					else
						return response('okay', 200);
					
			}

			/*
				The {Streamer} object in injected into the 
				controller method as a dependency.
			*/

			public function getNotifications(Request $request, Streamer $streamer){
			
			    $user = \Auth::user();
			    
			    return $streamer->setup(array(
			    	'as_event' => 'activity', // event name to listen for on the client-side
				'exec_limit' => 3000, // number of milliseconds allowed for streamer to collect data and send to the browser
				'as_cors' => TRUE // For Cross Origin requests
			    ))->send(
			        array(
			           'data_source_callback' => array(&$this, 'pullNotificationData'), // function/method to return notification data as array
			           'data_source_callback_args' => array(
			                    'args' => array($request, $user) // arguments to the `data_source_callback` method/function
			            )
			        )
			    );
			}
```

### Setup the EventServiceProvider to Configure Laravel events for Creating Notifications

```php

			/* In app/Providers/EventServiceProvider */

			class EventServiceProvider extends ServiceProvider {

				/**
				 * The event listener mappings for the application.
				 *
				 * @var array
				 */
				protected $listen = [
				    'Synergixe\PHPBeamzer\Events\NotificableEvent' => [
				        'App\Listeners\NotificableEventListener'
				    ],
				];
			}
```

### Setup Controller to fire Event when something happens within your Application

```php

			/* In app/Http/Controllers/MessageController.php */
			
			use Synergixe\PHPBeamzer\Events\NotificableEvent as NotificableEvent;

			class MessageController extends Controller {

				public function __construct(){

					// code goes here...
				}		
				
				public function fireNotificationEvent(Request $request, $kind) {

					$user = \Auth::user();
					
					$event = new NotificableEvent(
						$user, 
						$user->tasks()->where(
							'status', 
							$request->input('status')
						)->get()
					);
					
					$event->setKind($kind);

					event($event);
				}
			}
```

### Add the Modifier Traits (Actionable, Describable) to the Subject of your Notifications

```php

			/* In app/User.php */

			use Synergixe\PHPBeamzer\Modifiers\Actionable as Actionable;
			use Synergixe\PHPBeamzer\Modifiers\Describable as Describable;

			class User extends Eloquent {

				use Notifiable, Actionable, Describable;
				
				public function routeNotificationForMail(){
       
       					return $this->email_address;
    				}
				
				/* Override the `getDescription` method from trait { Describable } */
				
				public function getDecription($id){
				
					/* 
						This can be used to describe the subject/object each
						time on the client-side in your notifications
						 list when rendered in HTML
					*/
					return array(
						'name' => ($this->last_name . " " . $this->first_name),
						'id' => (is_null($id)? $this->uid : $id)
					);
				}
			}
			
```
### Modify the generated _NotificableEventListener_ to include your own code in the _handle_ method

```php

			/*In app/Listeners/NotificableEventListener.php */
			
			use Synergixe\PHPBeamzer\Notifications\ActivityStreamNotification as ActivityStreamNotification;
			use Synergixe\PHPBeamzer\Events\NotificableEvent as NotificableEvent;

			class NotificableEventListener implements ShouldQueue {

				use InteractsWithQueue;

				public function __construct(){

					// code goes here...
				}

				public function handle(NotificableEvent $event){
				
					$event_kind = $event->getKind();

					/* 
						The below code assumes that the {User} model has a 
						relationship called {followers} -> e.g. could be
						followers of a user on a social platform.

						So, all followers are notified using beamzers'
						custom notification {ActivityStreamNotification} with an
						action of 'asked to follow'.
					*/
					
					switch($event_kind){
					
						case "follows":
							$user->followers()->get()->each(function($follower, $index) use ($event) {
								$follower->notify(
								new ActivityStreamNotification(
									$event->producer->setActionPerformed(
										'asked to follow', 
										$event->timing
									),
									$event->reciever,
									$event->timing
								)
							    )->delay(
								\Carbon::now()->addMinutes(5);
							    );
							});
						break;
					}
				}

				public function failed(NotificableEvent $event, $exception){

			        	// code goes here...

			    	}
			}

```
### On the client-side, setup _beamzer-client JS libary_ like so

```html
<script src="path/to/beamzer-client.min.js"></script>

<script type="text/javascript">
	var beam = new BeamzerClient({
               source:"http://localhost:4001/users/notifications",
               params:{
                   id:"9UmxjXuu8yjI@jws8468#3"
               },
               options:{loggingEnabled:true, interval:2500}
          });
 
          beam.start(function onOpen(e){ 
	  	console.log("SSE connection established!");
	  }, onfunction onError(e){
	  	console.log("error: ", e.error);
	  }, function onMessage(e){
	  	console.log("message id: ", e.lastEventId);
	  	console.log("message data: ", e.data);
	  });
          beam.on("activity", function(e){
	  	console.log("event id: ", e.lastEventId);
	  	console.log("even data: ", e.data);
	  });
</script>

```

## License

MIT

## Requirement

- PHP 5.4.0 +
- Redis Server (Optional)

## Support

It isn't absolutely necessary but you can use this library with its front end component library called [beamzer-client](https://github.com/isocroft/beamzer-client/). This front end library support the follwoing browsers:

- IE 8.0+
- FF 4.0+
- Opera 10.5+
- Chrome 3.0+
- Safari 5.0+

## Contributing

You can contribute to this project by setting up a **DOCS** section or sending in a **PR**. report bugs and feature requests to the [issue tracker](https://github.com/synergixe/php-beamzer/issues)    
