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

        private $last_event_id;

        private $request;
	
	private $settings = array('as_event' => '', 'as_json' => TRUE);

        protected static $instance = NULL;


        public static function createStream(Request $request = NULL){

                static::$intance = new static($request);

                return static::$instance;
        }

        private function __construct(Request $request){

            $this->request = $request;

            $this->last_event_id = 0;

        }

        public function settings(array $settings){

		$this->settings = array_merge($this->settings, $settings);
        }

        public function start(array $options){

            $this->source_callback = $options['data_source_callback'];
            $this->source_callback_args = $options['data_source_callback_args'];
            $this->source_ops_interval = $options['data_source_ops_timeout'];
            
            if($this->request instanceof Request){
		 $ua = $this->request->headers->get('User-Agent');
		 if(preg_match('/MSIE/', $ua)){
		      // $this->request->query->add(['is_ie' => TRUE]);
		      $this->source_callback_args['is_ie'] = TRUE;
		 }   
                /*
			This assumes that the request object is from Laravel only (Illuminate\Http\Request)
		
			if($this->request->hasHeader('Last-Event-ID')){
			    $this->last_event_id = $this->request->header('Last-Event-ID', 0);
			}
		*/
		if($this->request->getSession()->get('beamzer:event_id') !== 0){
			$this->last_event_id = $this->request->headers->get('LAST_EVENT_ID');
			if(!isset($this->last_event_id)){
				$this->last_event_id = $this->request->query->('lastEventId');
			}
		}
                $this->request->getSession()->put('beamzer:event_id', $this->last_event_id);
            }else{
                @trigger_error('Symphony Request object is required to initialize');
            }

            $headers = array_merge(Stream::getHeaders(), array('Connection' => 'keep-alive', 'Access-Control-Allow-Origin' => '*'));

            $this->source_callback_args['next_id'] = $this->last_event_id + 1;

            $response = new StreamedResponse(array(&$this, 'stream_worker'));

	    foreach ($headers as $name => $value) {

		$response->headers->set($name, $value);
	    }
            
            return $response;

        }

        private function run_process($fn, $arr, $sets){
            
                    $sourceData = call_user_func_array($fn, $arr['args']);
		
		    $sourceData = method_exists($sourceData, 'toArray')? $sourceData->toArray() : $sourceData;

                    $stream = new Stream();

                    if($a['is_ie']){
                        $stream->addComment(str_repeat(" ", 2048)) // 2 kB padding for old IE (8/9)
                    }

                    if(count($sourceData)) === 0){
                        return $stream->setRetry(2000);
                                     ->end()
                                        ->flush();
                    }
		
		    $stream->setId($a['next_id']);
		
		    while (TRUE) {
			 
			    $event = $stream->event();
			    
			    if(!empty($sets['as_event'])){
                                $event->setEvent($sets['as_event'])
			    }
			    
			    $event->setData(($sets['as_json'] ? json_encode($sourceData) : $sourceData);
            				            
                    }
		
		    $tream->end()->flush();
            
        }

	

        private function register_cancellable_shutdown_function($callback){
                
                return new CancellableShutdownCallback(func_get_args());
        }

        private function stream_work(){

            	set_time_limit( 0 );
		
		ignore_user_abort(true);
            
		$this->run_process($this->source_callback, $this->source_callback_args, $this->settings);
            
        }

}

?>
