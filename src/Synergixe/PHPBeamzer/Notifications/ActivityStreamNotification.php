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

namespace Synergixe\PHPBeamzer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model as Model;
# use Illuminate\Notifications\Messages\MailMessage;

class ActivityStreamNotification extends Notification implements ShouldQueue {

  use Queueable;

  protected $subject;

  protected $object;

  protected $timestamp;

  public function __construct(Model $subject, Model $object = NULL, $timestamp = 0){

      if(trait_exists('Synergixe\PHPBeamzer\Modifiers\Actionable') 
                || trait_exists('Synergixe\PHPBeamzer\Modifiers\Describable')){
            $traits = class_uses($subject);
        
            if(!in_array(array('Synergixe\PHPBeamzer\Modifiers\Actionable', 
                               'Synergixe\PHPBeamzer\Modifiers\Describable'), $traits)){
                @trigger_error('Subject must be an object with {Actionable} and {Describable} traits');
            }
      }
    
    
      if(!is_null($object)){
          if(trait_exists('Synergixe\PHPBeamzer\Modifiers\Describable')){
                $traits = class_uses($object);

                if(!in_array('Synergixe\PHPBeamzer\Modifiers\Describable', $traits)){
                    @trigger_error('Object must be an object with {Describable} traits');
                }
          }
      }
    
      $this->subject = $subject;

      $this->object = $object;

      $this->timestamp = $timestamp;

  }

  public function via($notifiable){

    /*
        This will be enabled on next version
    
        return isset($notifiable->no_buzz) && $notifiable->no_buzz === TRUE 
                      ? [DBPushChannel::class] 
                      : ['mail', 'database']; */
    
        return ['database'];
  }

  public function toDatabase($notifiable){
    
    $obj = $this->object;
    
    if(!is_null($obj)){
        $obj = $this->object->getDescription($this->object->id);
    }else{
        $obj = 'new notification';
    }

    return [ 
      'subject' => $this->subject->getDescription($this->subject->id),
      'action' => $this->subject->getActionPerformed($this->timestamp),
      'object' => $obj
    ];
  }

  /*
       This will be enabled on the next version
  
        public function toMail($notifiable){
             if($notifiable->notifySuccessful('?')){ 
                 return (new MailMessage)->view(
                    'activity.mail', ['object' => $this->object]
                  );
             }else{
                  return  (new MailMessage)->error()
                      ->subject('Notification Subject')
                      ->line('...')
                      ->action('View Content', $this->object->getUrl())
                        ->line('***');
             }
        }
   */
}

?>
