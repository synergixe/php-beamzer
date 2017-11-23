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

      $this->subject = $subject;

      $this->object = $object;

      $this->timestamp = $timestamp;

  }

  public function via($notifiable){

    return isset($notifiable->no_buzz) && $notifiable->no_buzz === TRUE 
                      ? [DBPushChannel::class] 
                      : ['mail', 'database']; // important custom Channel defined here
  }

  public function toDatabase($notifiable){

    return [

      'subject' => $this->subject->id,
      'action' => $this->subject->getActionPerformed($this->timestamp),
      'object' => $this->object->id,
      'receiver' => $notifiable->id

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
                ->line('...')
                ->action('', '')
                  ->line('***');
       }
  }
}

?>
