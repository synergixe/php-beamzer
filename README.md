# Beamzer

This is a library that adds cross-browser support for real-time feeds and notifications to PHP web applications in an easy way (using Server-Sent Events (SSE) and COMET technologies only). It currently supports **Laravel** version 5.2 to 5.5 only. 

## How to Use

```bash

		$ composer require synergixe/php-beamzer

		$ php artisan make:event NotificableEvent

		$ php artisan make:listener NotificableEventListener --event="NotificableEvent"


```

```php

	/* In routes/web.php */
	Route::get('/users/notifications/{id}', 'EventSourceController@getNotifications');

	/* In app/Http/Controllers/EventSourceController.php */
	class EventSourceController extends Controller {

			public function __construct(){

				// code goes here...
			}

			private function pullNotificationStream($id){

				 $user = App\User::find($id);


				 return $user->unreadNotifications;

			}

			public function getNotifications(Request $request, $id, Beamzer $beam){
			    
			    return $beam->start(
			        array(
			           'data_source_callback' => array(&$this, 'pullNotificationStream'),
			           'data_source_ops_timeout' => 3000,
			           'data_source_callback_args' => array(
			                    'args' => array($id)
			            ),
			           'use_redis' => FALSE
			        )
			    );
			}

			/* In app/Http/Controllers/PostingsController.php */

			class PostingsController extends Controller {

				public function __construct(){

					// code goes here...
				}		
				
				public function publishArticle(Request $request) {

					$user = \Auth::user();

					Event::fire(NotificableEvent($request->all(), $user));
				}		
			}

			/* In app/Events/NotificableEvent.php */

			class NotificableEvent extends Event {

				public function __construct(){

					// code goes here...
				}

				public function broadcastOn(){

					return [];
				}
			}

			/*In app/Listeners/NotificableEventListener.php */

			class NotificableEventListener implements ShouldQueue {

				public function __construct(){

					// code goes here...
				}

				public function handle(NotificableEvent $event){

					$user->followers()->get()->each(function($user, $key) use ($event){
						$user->notify(
				        	new ActivityStream($event->producer->setActionPerformed('paid'), $event->content)
					    )->delay(
					        Carbon::now()->addMinutes(10);
					    );
					});
				}
			}

```

## License

MIT


## Addendum

I will be adding support on the client side with a simple JavaScript wrapper library called **BeamzerClient**. This library will depend on a polyfill for SSE (window.EventSource) maintained at this [repository](https://github.com/amvtek/EventSource/). Make sure you have the script at [this repository](https://github.com/isocroft/beamzer-client/) (**BeamzerClient**) before you use beamzer. This is because **Beamzer** and **BeamzerClient** work hand-in-hand.   
