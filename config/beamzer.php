<?php

return [

	'ping' => env('BEAMZER_PING_TIMEOUT', '3000'),
	
	'support_old_ie' => TRUE,
	
	'retry_limit_count' => 10,
	
	'use_redis_pubsub' => FALSE,
	
	'redis_pub_channel' => 'notifications',
	
	'data_chunks_size' => 5

];

?>
