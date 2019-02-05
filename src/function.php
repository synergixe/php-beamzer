
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

if(! function_exists('object_uses_trait')){
	/**
	 * @See: https://eliasvo.wordpress.com/2015/06/07/php-traits-if-they-werent-evil-theyd-be-funny/
	 *
	 *
	 * @param object $object
	 * @param string $trait
	 * @return bool
	 */
	
	function object_uses_trait($object, $trait){
		
		// check if trait class actually exists and if the object is set
		if(! trait_exists($trait) || is_null($object)){
			return false;
		}
		
		// check if the class uses the trait in it's definition
		$used = class_uses($object);
		
		// short_class_name($trait);
		
		if(! isset($used[$trait])){
			$parents = class_parents($object);
			while(!isset($used[$trait])
			     	&& $parents){
				// get trait(s) used by parents
				$used = class_uses(array_pop($parents));
			}
		}
		
		return isset($used[$trait]);
	}
	
}


if(! function_exists('short_class_name')){

	function short_class_name($class_name){
		
		$short_name = (substr($class_name, strrpos($class_name, '\\') + 1));
		
		return $short_name;
	}
}

if(! function_exists('cancel_shutdown_function') ){
	function cancel_shutdown_function($envelope){
    		if(method_exists($envelope, 'cancel')){
    			$envelope->cancel();
		}
	}
}

if(! function_exists('normalize_laravel_notifications') ){
	
  function normalize_laravel_notifications($_row){
	  
	$row = is_array($_row) ? $_row : array('data'=>'[]','id'=>'','created_at'=>'','read_at'=>NULL);
	  
  	$item = array();
	
  	$item['payload'] = @json_decode($row['data'], TRUE);
	  
	$item['nid'] = $row['id'];
	$item['timing'] = $row['created_at'];
	$item['is_read'] = !(is_null($row['read_at']));
	  
	return $item;
  }
}

if(! function_exists('config') ){
	function config ($key) {
		
		return "-";
	}
}

?>
