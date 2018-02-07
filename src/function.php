
<?php

if(! function_exists('cancel_shutdown_function') ){
	function cancel_shutdown_function($envelope){
    		
    		$envelope->cancel();
	}
}

if(! function_exists('normalize_laravel_notifications') ){
  function normalize_laravel_notifications ($row){
	$item = json_decode($row['data'], TRUE);
	$item['nid'] = $row['id'];
	$item['timing'] = $row['created_at'];
	$item['is_read'] = ($row['read_at'] !== NULL);
	  
	return $item;
  }
}

if(! function_exists('config') ){
	function config ($key) {
		
		return "";
	}
}

?>
