
<?php

if(!function_exists('cancel_shutdown_function')){
	function cancel_shutdown_function($envelope){
    		
    		$envelope->cancel();
	}
}

if(!function_exists('getobject')){
	function getobject($resolution_name){
		if(class_exists($resolution_name)
		   && is_callable('resolve')){
			return resolve($resolution_name);
		}

		return NULL;
	}
}

?>