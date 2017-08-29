<?php

namespace Beamzer\Notifications;

/*use Illuminate\Bus\Queueable;*/
use Illuminate\Notifications\Notification;
/*use Illuminate\Contracts\Queue\ShouldQueue;*/
use Illuminate\Database\Eloquent\Model;

use Beamzer\Notifications\DBPushChannel as DBPushChannel;

class ActivityStream extends Notification /* implements ShouldQueue*/ {

  /*use Queueable;*/

  protected $subject;

  protected $object;

  public function __construct(Model $subject, Model $object){

      $this->subject = $subject;

      $this->object = $object;

  }

  public function via($notifiable){

    return isset($notifiable->no_buzz) && $notifiable->no_buzz === TRUE 
                      ? [DBPushChannel::class] 
                      : ['mail', 'database']; // important custom Channel defined here
  }

  public function toDatabase($notifiable){

    return [

      'subject' => $subject->id,
      'action' => $subject->getActionPerformed($this->object),
      'object' => $this->object->id,
      'url' => $this->object->getActionablePath($notifiable, $this->object)

    ];
  }

  public function toMail($notifiable){
       if($notifiable->notifySuccessful('?')){ 
    	     return (new MailMessage)->view(
              'activity.mail', ['object' => $this->object]
            );
       }else{
            return  (new MailMessage)->error()
                ->subject('Notification Subject')
                ->line('...');;
       }
  }
}

?>