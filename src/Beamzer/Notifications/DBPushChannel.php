<?php

namespace Beamzer\Notifications;

use Illuminate\Notifications\Notification;

class DBPushChannel {

   public function send($notifiable, Notification $notification){

	    $data = $notification->toDatabase($notifiable);

	    return $notifiable->routeNotificationFor('database')->create([

	        'id' => $notification->id,
	        'user_id' => $notifiable->id,
	        'category' => 'activity_stream',
	        'type' => get_class($notification),
	        'data' => json_encode($data),  //<-- comes from toDatabase() Method above
	        'read_at' => null,
	        'created_at' => time(),
	        'deleted_at' => null

	    ]);
  }
}

?>