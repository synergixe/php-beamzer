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

namespace Synergixe\PHPBeamzer\Notifications;

use Synergixe\PHPBeamzer\Channels\DBPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model as Model;
use Synergixe\PHPBeamzer\Modifiers\Actionable;
use Synergixe\PHPBeamzer\Modifiers\Describable;

# use Illuminate\Notifications\Messages\MailMessage;

class ActivityStreamNotification extends Notification implements ShouldQueue {

  use Queueable;

  protected $subject;
  
  protected $verb;

  protected $object;

  protected $timestamp;

  public function __construct(Model $subject, Model $object = NULL, $timestamp, $kind){

        
            if(! object_uses_trait($subject, Actionable::class)
                  || ! object_uses_trait($subject, Describable::class)){
                @trigger_error('Subject must be an object with {Actionable} and {Describable} traits');
            }
      
            if(! object_uses_trait($object, Describable::class)){
                    @trigger_error('Object must be an object with {Describable} traits');
            }
    
            $this->subject = $subject;
            $this->object = $object;
    
            $this->verb = $this->subject->getActionPerformed(
		$kind
            );
            $this->subject->setDescription($kind);
            $this->object->setDescription($kind);

  }

  public function via($notifiable){

    /*
        This will be enabled on next version
    
        return isset($notifiable->no_buzz) && $notifiable->no_buzz === TRUE 
                      ? [DBPushChannel::class] 
                      : ['mail', 'database']; 
    */
    
        return ['database'];
  }

  public function toDatabase($notifiable){
    
        $object = $this->object->getDescription();
        $subject = $this->subject->getDescription();
    
        return [ 
          'subject' => $subject,
          'action' => $this->verb,
          'unix_timestamp' => $this->timestamp,
          'object' => $object
        ];
  }

  
  public function toMail($notifiable){
    
        return  (new MailMessage)
                ->greeting('Hello '. $notifiable->getDescription() .'!')
                ->subject('Notification Alert')
                ->line('You have a new notification from ' . config('app.name'))
                /*->action('View Detail', $this->object->getUrl())*/
                ->line('Thank you for using our application');
             
  }
}

?>
