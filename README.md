# PHPBeamzer

[![Latest Version](https://img.shields.io/github/release/synergixe/php-beamzer.svg?style=flat-rounded)](https://github.com/synergixe/php-beamzer/releases)
[![Build Status](https://travis-ci.org/synergixe/php-beamzer.svg?branch=master)](https://travis-ci.org/JoggApp/laravel-natural-language)
[![Total Downloads](https://img.shields.io/packagist/dt/synergixe/php-beamzer.svg?style=flat-rounded&colorB=brightgreen)](https://packagist.org/packages/synergixe/php-beamzer)

This is a library that adds cross-browser support for real-time feeds and notifications to PHP web applications in an easy way (using Server-Sent Events (SSE) only). It currently supports **Laravel** version 5.4, 5.5, 5.6 and 5.7 only. 

## How to Use

### Load Up the package from Composer

```bash

		$ composer require synergixe/php-beamzer:^0.1

```

```json
	
	"require": {
        	"synergixe/php-beamzer": "^0.1"
    	}
```

### Publish the config for the package to the Laravel config folder

```bash
php artisan vendor:publish --provider="Synergixe\Beamzer\Providers\Laravel\BeamzerServiceProvider"
```

#### This will create the package's config file called `beamzer.php` in the `config` directory of your Laravel Project. These are the contents of the published config file:

```php

return [
	    /*
	    |--------------------------------------------------------------------------
	    | The timeout value for when Beamzer should stop trying tofetch data (milliseconds)
	    |--------------------------------------------------------------------------
	    */
	
	'ping' => env('BEAMZER_PING_TIMEOUT', '3000'),
	    /*
	    |--------------------------------------------------------------------------
	    | To support IE 8/9 or not when sending data back to the client.
	    |--------------------------------------------------------------------------
	    */
	
	'support_old_ie' => TRUE,
	    /*
	    |--------------------------------------------------------------------------
	    | Number of times before the client should retry an SSE connection.
	    |--------------------------------------------------------------------------
	    */
	
	'retry_limit_count' => 10,
	    /*
	    |--------------------------------------------------------------------------
	    | To use or not to use Redis.
	    |--------------------------------------------------------------------------
	    */
	'use_redis' => FALSE,
	    /*
	    |--------------------------------------------------------------------------
	    | redis publish channel name.
	    |--------------------------------------------------------------------------
	    */
	'redis_pub_channel' => 'notifications',
	    /*
	    |--------------------------------------------------------------------------
	    | The size of data sent back to the client per connection.
	    |--------------------------------------------------------------------------
	    */
	'data_chunks_size' => 5

];
```

### Create the event listener to be used with the nofication custom event using the custom command provided by the package

```bash
php artisan create:notificationfile
```

### Then create your Laravel Controllers

```bash
php artisan make:controller EventSourceController
		
php artisan make:controller MessageController
```

### Create the Laravel Notifications Database Table

```bash
php artisan notifications:table

php artisan migrate
```

### Register the route for returning your stream notifications and for create notifications

```php

	/* In routes/web.php */

	Route::get('/users/notifications', 'EventSourceController@getNotifications');
	Route::post('/notify/subjects/{kind}', 'MessageController@fireNotificationEvent');
	Route::patch('/user/notifications/update/{nid}', 'EventSourceController@updateNotificationsAsRead');
	
```

### Update the app config for service provider and alias classes 

> If you use Laravel 5.5 and above don't bother doing this as it is included automatically

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
	
	use Synergixe\PHPBeamzer\Beamzer as Beamzer;

	class EventSourceController extends Controller {

			public function __construct(){

				// code goes here...
			}

			public function pullNotificationData(Request $request, $user){

				 if(!isset($user)){
				 	return array();
				 }

				 $last_id = $request->input('lastEventId');
				 
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
				The {Beamzer} object in injected into the 
				controller method as a dependency.
			*/

			public function getNotifications(Request $request, Beamzer $streamer){
			
			    $user = \Auth::user();
			    
			    $response = $streamer->setup(array(
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
			    
			    return $response;
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
			
			use Illuminate\Http\Request;
			use Synergixe\PHPBeamzer\Events\NotificableEvent as NotificableEvent;

			class MessageController extends Controller {

				public function __construct(){

					// code goes here...
				}		
				
				public function fireNotificationEvent(Request $request, $kind) {
					$event = null;
					
					switch($kind){
						case "follows":
							$user = \Auth::user();
							$followee = User::where('id', '=', $request->input('followee_id'));

							$follow = $user->followings()->save([
								'follower_id' => $user->id,
								'followee_id' => $followee->id
							]);

							$event = new NotificableEvent(
								$user, 
								$followee
							);
						break;
					}
				
					if(! is_null($event)){
						$event->setKind($kind);
					
						event($event);
					}
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
				
				/* create the `actionsPerfomed` property for trait { Actionable } */
				
				protected $actionsPerfomed = array(
					"follows" => 'asked to follow you'
				);
				
				public function routeNotificationForMail(){
       
       					return $this->email_address;
    				}
				
				public function followings(){ // relation for all `followings`
				
					return $this->belongsToMany(User::class,
						    'social_network_follows',
						    'followee_id', 'follower_id', 'id', 'id', 'followings'
					)->withTimestamps();
				}
				
				/* create the `makeDescription` method for trait { Describable } */
				
				public function makeDecription(){
				
					/* 
						This can be used to describe the subject/object each
						time on the client-side in your notifications
						 list when rendered in HTML
					*/
					
					return array(
						'name' => ($this->last_name . " " . $this->first_name),
						'id' => $this->id
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
				
					$event->__wakeup();
				
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
						
							$event->reciever->notify(
								new ActivityStreamNotification(
									$event->producer,
									$event->reciever,
									$event->timstamp,
									$event_kind
								)
							)->delay(
								\Carbon::now()->addMinutes(5);
							);
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
	  	console.log("error: ", e);
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

- PHP 5.6.4 +
- Redis Server (Optional)

## Support

It isn't absolutely necessary but you can use this library with its front end component library called [beamzer-client](https://github.com/isocroft/beamzer-client/). This front end library support the follwoing browsers:

- IE 9.0+
- FF 4.0+
- Opera 10.5+
- Chrome 4.0+
- Safari 7.0+

## Contributing

You can contribute to this project by setting up a **DOCS** section or sending in a **PR**. report bugs and feature requests to the [issue tracker](https://github.com/synergixe/php-beamzer/issues)    
