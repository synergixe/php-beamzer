<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.8
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

  namespace Synergixe\PHPBeamzer\Modifiers;

  trait Describable {
    
            protected $notification_desc;
    
            public function setDescription($event_kind){
              
                  if(! $this->exists){
                      $this->notification_desc = null;
                      return;
                  }
            
                  if(method_exists($this, 'makeDescription')){
                        $this->notification_desc = $this->makeDescription($event_kind);
                  }
    
            }
    
            public function getDescription(){
            
                  return $this->notification_desc;
            }
    
    }
?>
