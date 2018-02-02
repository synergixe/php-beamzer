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

  namespace Synergixe\PHPBeamzer\Modifiers;

  trait Describable {
    
            public function getDescription($id){
            
                  return is_null($id)? '-' : $id;
            }
    
    }
?>
