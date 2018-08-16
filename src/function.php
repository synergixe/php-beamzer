
<?php

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
		// check arguments
		$used = class_uses($object);
		if(!isset($used[$trait])){
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

/*if(! function_exists('is_actionable')){

	function is_actionable($class_name, $key){
		
		$short_name = (substr($class_name, strrpos($class_name, '\\') + 1));
		
		return ($short_name === 'Actionable');
	}
}

if(! function_exists('is_describable')){

	function is_describable($class_name, $key){
		
		$short_name = (substr($class_name, strrpos($class_name, '\\') + 1));
		
		return ($short_name === 'Describable');
	}
}*/

if(! function_exists('cancel_shutdown_function') ){
	function cancel_shutdown_function($envelope){
    		if(method_exists($envelope, 'cancel')){
    			$envelope->cancel();
		}
	}
}

if(! function_exists('normalize_laravel_notifications') ){
  function normalize_laravel_notifications ($_row){
	  
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
