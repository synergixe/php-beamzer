<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.3
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer\Channels;

use Illuminate\Notifications\Notification;

class DBPushChannel {
  
	public function __construct(){
		
    		// code..
    
	}	
  
	public function send($notifiable, Notification $notification){

		    $data = $notification->toDatabase($notifiable);

		    return $notifiable->routeNotificationFor('database')->create([
			'id' => $notification->id,
			'type' => get_class($notification),
			'data' => json_encode($data),  //<-- comes from toDatabase() Method above
			'read_at' => null,
			'created_at' => time(),
			'deleted_at' => null
		    ]);
	}
}
?>
