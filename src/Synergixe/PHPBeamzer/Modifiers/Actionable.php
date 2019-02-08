<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.9
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer\Modifiers;

trait Actionable {
  
	public function getActionPerformed($kind){
    
		return property_exists($this, 'actionsPerfomed')
				&& array_key_exists($kind, $this->actionsPerfomed)
			 ? $this->actionsPerfomed[$kind] 
      
			 : 'interacted with'
		);
    
	}
}

?>
