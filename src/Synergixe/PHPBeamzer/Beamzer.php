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
use Symfony\Component\HttpFoundation\StreamedResponse as StreamedResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Synergixe\PHPBeamzer\Helpers\CancellableShutdownCallback as CancellableShutdownCallback;

class Beamzer {

        private $source_callback;

        private $source_callback_args;

        private $source_ops_interval;

        private $req_count;

        private $request;
	
	private $settings = array('as_event' => '', 'ignore_id' => FALSE);

        protected static $instance = NULL;


        public static function createStream(Request $request = NULL){

                static::$intance = new static($request);

                return static::$instance;
        }

        private function __construct(Request $request){

            $this->request = $request;

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
		
	    ignore_user_abort(true);

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

        private function run_process($fn, $arr, $sets){
            
                    $sourceData = call_user_func_array($fn, $arr['args']);
		
		    $sourceData = method_exists($sourceData, 'toArray')? $sourceData->toArray() : $sourceData;
		
		    if(!is_array($sourceData)){
		    	;
		    }
		
		    $data = array_map("normalize_laravel_notifications", $sourceData);
		
		    $chunks = array_chunk($data, 5, true);

                    $stream = new Stream();

                    if(count($sourceData)) === 0){
                        return $stream->setRetry(10000);
			    		->setData('{"status":"empty"}')
                                     	->end()
					->flush();
                    }
		
		    $last_item = end($data);
		
		    $stream->setId($last_item['nid']);
		
		    $this->request->getSession()->put('beamzer:request_count', 0);
		
		    while (TRUE) {
			 
			    if(connection_aborted()){
				 exit();
			    }
			    
			    $event = $stream->event();
			    
			    if($a['is_ie']){
				$event->addComment(str_repeat(" ", 2048)) // 2 kB padding for old IE (8/9)
			    }
			    
			    if(!empty($sets['as_event'])){
                                $event->setEvent($sets['as_event'])
			    }
			    
			    $event->setData(json_encode(array_shift($chunks)));
            				            
                    }
		
		    $tream->end()->flush();
            
        }

	

        private function register_cancellable_shutdown_function($callback){
                
                return new CancellableShutdownCallback(func_get_args());
        }

        private function stream_work(){

            	set_time_limit( 0 );
            
		$this->run_process($this->source_callback, $this->source_callback_args, $this->settings);
            
        }

}

?>
