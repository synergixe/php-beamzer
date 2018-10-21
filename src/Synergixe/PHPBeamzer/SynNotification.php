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
 
 namespace Synergixe\PHPBeamzer;

use Illuminate\Notifications\DatabaseNotification;

class SynNotification extends DatabaseNotification {
 
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_stream_notifications';
 
    protected $appends = [];
    
    public function getActivitySubject(){
      
         if(! $this->exists){
              return null;
         }
     
         return $this->data['subject'];
    }
 
    public function getActivityVerb(){
      
         if(! $this->exists){
              return null;
         }
     
         return $this->data['action'];
    }
}
