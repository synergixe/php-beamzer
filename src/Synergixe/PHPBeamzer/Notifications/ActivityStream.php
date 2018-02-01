<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.1
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer\Notifications;

/*use Illuminate\Bus\Queueable;*/
use Illuminate\Notifications\Notification;
/*use Illuminate\Contracts\Queue\ShouldQueue;*/
use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Notifications\Messages\MailMessage;

class ActivityStream extends Notification /*implements ShouldQueue */ {

  /*use Queueable;*/

  protected $subject;

  protected $object;

  protected $timestamp;

  public function __construct(Model $subject, Model $object, $timestamp){

      if(trait_exists('Actionable')){
            $traits = class_uses($subject);
        
            if(!in_array('Actionable', $traits)){
                @trigger_error('Subject must be an object with {Actionable} traits');
            }
      }
    
      if(trait_exists('Describable')){
            $traits = class_uses($object);
        
            if(!in_array('Describable', $traits)){
                @trigger_error('Object must be an object with {Describable} traits');
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

    return [ 
      'subject' => $this->subject->getDescription(),
      'action' => $this->subject->getActionPerformed($this->timestamp),
      'object' => $this->object->getDescription()

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
