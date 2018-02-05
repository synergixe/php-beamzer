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

        private $req_count;

        private $request;
	
	private $redis;
	
	private $tick;
	
	private $settings;
	
	private $cors_headers;
	
	protected $cancellable;

        protected static $instance = NULL;


        public static function createInstance(Request $request = NULL, RedisConnection $redis_connection = NULL){

                if(is_null(static::$instance)){
			static::$intance = new static($request, $redis_connection);
		}

                return static::$instance;
        }

        private function __construct(Request $request, RedisConnection $redis){

            $this->request = $request;
		
	    $this->redis = $redis;

            $this->req_count = 0;
		
	    $this->tick = -1;
		
	    $this->settings = array(
		    'as_event' => '', 
		    'ignore_id' => FALSE, 
		    'exec_limit' => 900, 
		    'as_cors' => FALSE, 
		    'is_ie' => FALSE
	    );
		
	    $this->cors_headers = array(
		    'Access-Control-Allow-Credentials' => 'true', 
		    'Access-Control-Allow-Origin' => '*'
	    );
		
	    $this->cancellable = NULL;

        }
	
	protected function getConfig($aspect = ''){
	
		$beamzerConfigs = config('beamzer');

		if(array_key_exists($aspect, $beamzerConfigs)){
			return $beamzerConfigs[$aspect];
		}
		
		return $beamzerConfigs;
	}

        public function setup(\array $settings){

		$this->settings = array_merge($this->settings, $settings);
		
		return $this;
        }
	
	protected function get_exec_time_diff($timecount){
        
		return ($timecount - $this->tick);
	}

	protected function sleep($time){

	    usleep($time * 1000000);
	}

	protected function is_tick_elapsed(){
		
		$exec_limit = $this->settings['exec_limit'];

		return ($exec_limit !== 0 
			&& ($this->get_exec_time_diff(time()) > $exec_limit));
	}

        public function send(\array $options){
	
	    @set_time_limit( 0 );
		
	    @ignore_user_abort( true );

            $this->source_callback = $options['data_source_callback'];
            $this->source_callback_args = $options['data_source_callback_args'];
            
            if($this->request instanceof Request){
		 
		    	if($this->getConfig('support_old_ie') === TRUE){
				
			    $ua = $this->request->headers->get('User-Agent');

			    if(preg_match('/MSIE/', $ua)){

				 $this->settings['is_ie'] = TRUE;
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
		
	    $headers = array(
		    'Connection' => 'keep-alive', // Instruct/Implore the browser to keep the TCP/IP connection open
		    'X-Accel-Buffering' => 'no' // Disable FastCGI Buffering on Nginx 
	    );
		
	    if($this->settings['as_cors']){
	    	$headers = array_merge($headers, $this->cors_headers);
	    }

            $headers = array_merge(Stream::getHeaders(), $headers);

            $response = new StreamedResponse(array(&$this, 'stream_work'), 200, $headers);

	    /*foreach ($headers as $name => $value) {

		$response->headers->set($name, $value);
		
	    }*/
            
            return $response->send();

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
	
	protected function onPublish($sets){
		
		$channel = $this->getConfig('redis_pub_channel');
		
		$client = $this->redis->client();
		
		$that = $this;
		
	     	$stream = new Stream();
	
		$this->redis->subscribe($channel, function($payload) use ($stream, $client, $that){
			
			// @TODO: Store in Redis DB for delay (Redis has a timer task that persists data in memory to disk... so we can exploit that)
			# client->lpush('beamzer:data', $payload);
			
			if(connection_aborted()){
				 if(!is_null($that->cancellable)){   
				 	cancel_shutdown_function($this->cancellable);
				 }
			 }
			
			$event = $stream->event();
			
		    	$event->setId(time());
			
			if(!empty($sets['as_event'])){
                                $event->setEvent($sets['as_event'])
			    }
			
			$event->setData($payload)
				$event->end()
					->flush();
		});
		    
	}

        protected function onEvent($fn, $arr, $sets){

		$noupdate = FALSE;

		$tryupdate_count = 0;

		$event = NULL;

		while(TRUE){

			/*
				HTTP Clients can disconnect from the server at will
				so, we always need to check up on them.

				if the browser disconnected abruptly, then cancel
				the PHP shutdown function so that no data is sent
			*/

			if(connection_abroted()){
				
				if(!is_null($this->cancellable)){   
				 	cancel_shutdown_function($this->cancellable);
				}

				break;
			}

			/*
				If there is no data update from the previous iteration
				then, we need to idle the running process for 0.9 secs

				if we have a reference to the `$event` object from the
				previous iteration and we have tried twice before with
				no success on a data update 

				then, we tell the browser to retry later.
			*/

			if($noupdate){

				$noupdate = FALSE;

				$this->sleep(0.9);

				if(!is_null($event) 
					&& $tryupdate_count == 2){
					$event->retry((1000 * $this->req_count))
						->addComments('heartbeat')
							->end()
							   ->flush();
						
				}

				++$tryupdate_count;
			}

			$chunks = array();

			/*
			   If and when the time allowed for execution of outer
			   iteration 
			*/

			if($this->is_tick_elapsed() 
				|| $tryupdate_count > 2){
				
				break;
			}

			$sourceData = call_func_array($fn, $arr['args']);

			$data = $this->extractData($sourceData, $chunks);

			$event = (new Stream)->event();

			if(count($data) == 0){

				$noupdate = TRUE;

				$max_req_count = intval($this->getConfig('retry_limit_count'));

				if($this->req_count >= $max_req_count){
				
					$this->req_count = 0;
			    		$this->request->getSession()->put('beamzer:request_count', $this->req_count);
			    	}

				/* 
					We had initially set the `Connection` response header to 'keep-alive'
					However, HTTP clients (e.g. browser) are at liberty to disregard
					this 'keep-alive' value and disconnect at will - according to the 
					W3C HTTP Specs.

					Since there isn't an update that we can send to the browser as {data},

					We do earnestly need the connection open for a little longer
					thus, we send out SSE comment data to trick the browser into 
					expecting something more; forcing it to keep the connection alive/open.

					so, the browser can still recieve data - maybe OR maybe not.

					See: https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
				*/
				$event->addComments(sha1(mt_rand()))
						->end()
						   ->flush();

				continue;

			}


			$last_notification = end($data);

			$id = $last_notification['timing'];

			/*
				We need to update the Request input data
				for the next outermost iteration.
				
				This will enable us read fresh notification
				data from the DB using the 'created_at'
				timestamp column/field
			*/
			$this->request->query->set('lastEventId', $id);

			while(count($chunks) != 0){
				
				$event->setId($id)
					->setEvent($sets['as_event'])
				
				if($a['is_ie']){
					$event->addComment(str_repeat(";", 2048)) // 2 kB padding for old IE (8/9)
			    	}
				
				$event->setData(
					json_encode(
						array_shift($chunks)
					))->end()
						->flush();
			}

			$noupdate = TRUE;	
		}

		// exit();
    	}

	

        private function run_in_background($callback){
                
                $this->cancellable = new CancellableShutdownCallback(func_get_args());
        }

        private function stream_work(){

		$this->tick = time();
		
		/*
			prevent buffering from taking place
		*/
		
		if(function_exists('apache_setenv')){
		    @apache_setenv('no-gzip', 1);
		}
		
		$can_use_redis = $this->getConfig('use_redis');
            
		if(is_null($this->redis) 
		   	&& !$can_use_redis){
			
			// $this->onEvent($this->source_callback, $this->source_callback_args, $this->settings);
			
			$this->run_in_background(array(&$this, 'onEvent'), array($this->source_callback, $this->source_callback_args, $this->settings));
			
		}else{
			
			// $this->onPublish($this->settings);
			
			$this->run_in_background(array(&$this, 'onPublish'), array($this->settings)
		}
            
        }

}

?>
