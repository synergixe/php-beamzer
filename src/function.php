
<?php

if(!function_exists('cancel_shutdown_function')){
	function cancel_shutdown_function($envelope){
    		
    		$envelope->cancel();
	}
}



?>