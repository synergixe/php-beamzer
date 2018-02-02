<?php

namespace Synergixe\PHPBeamzer\Modifiers;

trait Actionable {
  
	public $no_buzz = FALSE;
  
	private $actions_perfomed = array();
  
	public function setActionPerformed($user_action = 'did', $timestamp) {
    
		$timestamp = strval($timestamp);
    
		$this->actions_perfomed[$timestamp] = $user_action;

    return $this;
	}
  
	public function getActionPerformed($timestamp){
    
		return (
				array_key_exists($timestamp, $this->actions_perfomed)
      
			 ? $this->actions_perfomed[$timestamp] 
      
			 : NULL
		);
    
	}
}

?>
