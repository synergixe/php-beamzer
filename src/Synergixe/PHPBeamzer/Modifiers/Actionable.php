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

namespace Synergixe\PHPBeamzer\Modifiers;

trait Actionable {
  
	public function getActionPerformed($kind){
    
		return property_exists($this, 'actions_perfomed')
				&& array_key_exists($kind, $this->actions_performed)
			 ? $this->actions_perfomed[$kind] 
      
			 : 'interected with'
		);
    
	}
}

?>
