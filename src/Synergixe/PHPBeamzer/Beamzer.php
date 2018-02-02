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

namespace Synergixe\PHPBeamzer;

use Igorw\EventSource\Stream as Stream;
use Illuminate\Redis\Connections\Connection as RedisConnection;
use Symfony\Component\HttpFoundation\StreamedResponse as StreamedResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Synergixe\PHPBeamzer\Helpers\CancellableShutdownCallback as CancellableShutdownCallback;

class Beamzer {

        private $source_callback;

        private $source_callback_args;

        private $source_ops_interval;

        private $req_count;

        private $request;
	
	private $redis;
	
	private $settings = array('as_event' => '', 'ignore_id' => FALSE);

        protected static $instance = NULL;


        public static function createStream(Request $request = NULL, RedisConnection $redis_connection = NULL){

                if(is_null(static::$instance)){
			static::$intance = new static($request, $redis_connection);
		}

                return static::$instance;
        }

        private function __construct(Request $request, RedisConnection $redis){

            $this->request = $request;
		
	    $this->redis = $redis;

            $this->req_count = 0;

        }
	
	protected function getConfig($aspect = ''){
	
		$beamzerConfigs = config('beamzer');

		if(array_key_exists($aspect, $beamzerConfigs)){
			return $beamzerConfigs[$aspect];
		}
		
		return $beamzerConfigs;
	}

        public function setup(array $settings){

		$this->settings = array_merge($this->settings, $settings);
        }

        public function start(array $options){
		
	    ignore_user_abort( true );

            $this->source_callback = $options['data_source_callback'];
            $this->source_callback_args = $options['data_source_callback_args'];
            $this->source_ops_interval = $options['data_source_ops_timeout'];
            
            if($this->request instanceof Request){
		 
		    	if($this->getConfig('support_old_ie') === TRUE){
				
			    $ua = $this->request->headers->get('User-Agent');

			    if(preg_match('/MSIE/', $ua)){

				 $this->source_callback_args['is_ie'] = TRUE;
			    }   
				
			}
                
		    
		    	$count = intval($this->request->getSession()->get('beamzer:request_count'));
			
		    	if(!isset($count)){
				$count = 1;
			}else{
				++$count;
			}
		    
		    	$this->req_count = $count;
		    	$this->request->getSession()->put('beamzer:request_count', $this->req_count);
            }else{
                @trigger_error('Symphony Request object is required to initialize');
            }

            $headers = array_merge(Stream::getHeaders(), array('Connection' => 'keep-alive', 'Access-Control-Allow-Origin' => '*'));

            $response = new StreamedResponse(array(&$this, 'stream_work'));

	    foreach ($headers as $name => $value) {

		$response->headers->set($name, $value);
	    }
            
            return $response;

        }
	
	private function extractData($sourceData, &$chunks){
	
		    $sourceData = method_exists($sourceData, 'toArray')? $sourceData->toArray() : $sourceData;
		
		    if(!is_array($sourceData)){
		    	$sourceData = (array) $sourceData;
		    }
		
		    $data = array_map("normalize_laravel_notifications", $sourceData);
		
		    $chunk_size = intval($this->getConfig('data_chunks_size'));
		
		    if($chunk_size > 10){
		    	$chunk_size = 10;
		    }
		
		    $chunks = array_chunk($data, $chunk_size, true);
		
		    return $data;
	}
	
	private function onPublish($fn, $arr, $sets){
		
		$channel = $this->getConfig('redis_pub_channel');
		
		$client = $this->redis->client();
			
			// Store in Redis DB for delay
			# client->set('', );
		
		    $stream = new Stream();
	
		$this->redis->subscribe($channel, function($payload) use ($stream){
			
			$event = $stream->event();
			
		    $event->setId(time());
			
			if(!empty($sets['as_event'])){
                                $event->setEvent($sets['as_event'])
			    }
			
			$event->setData($payload)
				$event->end()
					->flush();
		    
	}

        private function onEvent($fn, $arr, $sets){
            
                    $sourceData = call_user_func_array($fn, $arr['args']);
		
		    $chunks = array();
		
		    $data = $this->extractData($sourceData, $chunks);
		
		    $max_req_count = intval($this->getConfig('retry_limit_count'));

                    $stream = new Stream();

                    if(count($sourceData)) === 0){
                        return $stream->setRetry((1000 * $this->req_count));
			    		->setData('{"heartbeat":true}')
                                     	->end()
					->flush();
                    }
		
		    $last_item = end($data);
		
		    if($this->req_count >= $max_req_count){
			
			$this->req_count = 0;
		    	$this->request->getSession()->put('beamzer:request_count', $this->req_count);
		    }
		
		    while (count($chunks) != 0) {
			 
			    if(connection_aborted()){
				 if(!is_null($this->cancellable)){   
				 	cancel_shutdown_function($this->cancellable);
				 }
				 exit();
			    }
			    
			    $event = $stream->event();
			    
			    $event->setId($last_item['timing']);
			    
			    if($a['is_ie']){
				$event->addComment(str_repeat(" ", 2048)) // 2 kB padding for old IE (8/9)
			    }
			    
			    if(!empty($sets['as_event'])){
                                $event->setEvent($sets['as_event'])
			    }
			    
			    $event->setData(json_encode(array_shift($chunks)));
            				            
                    }
		
		    $stream->end()->flush();
            
        }

	

        private function run_in_background($callback){
                
                $this->cancellable = new CancellableShutdownCallback(func_get_args());
        }

        private function stream_work(){

            	set_time_limit( 0 );
		
		$can_use_redis = $this->getConfig('use_redis');
            
		if(is_null($this->redis) 
		   	&& $can_use_redis){
			
			// $this->onEvent($this->source_callback, $this->source_callback_args, $this->settings);
			
			$this->run_in_background(array(&$this, 'onEvent'), array($this->source_callback, $this->source_callback_args, $this->settings));
			
		}else{
			
			$this->onPublish($this->source_callback, $this->source_callback_args, $this->settings);
		}
            
        }

}

?>
