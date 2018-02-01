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

        protected static $instance = NULL;


        public static function createStream(Request $request = NULL){

                static::$intance = new static($request);

                return static::$instance;
        }

        private function __construct(Request $request){

            $this->request = $request;

            $this->last_event_id  = 0;

        }

        public function settings(array $settings){

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
		$this->last_event_id = $this->request->headers->get('LAST_EVENT_ID');
		if(!isset($this->last_event_id)){
                	$this->last_event_id = $this->request->query->('lastEventId');
		}
                $this->request->getSession()->put('event_id', $this->last_event_id);
            }else{
                @trigger_error('Symphony Request object is required to initialize');
            }

            $headers = array_merge(Stream::getHeaders(), array('Connection' => 'keep-alive', 'Access-Control-Allow-Origin' => '*'));

            $this->source_callback_args['last_id'] = $this->last_event_id;

            $response = new StreamedResponse(array(&$this, 'stream_worker'));

	    foreach ($headers as $name => $value) {

		$response->headers->set($name, $value);
	    }
            
            return $response;

        }

        private function run_process($fn, $arr){
            
                    $sourceData = call_user_func_array($fn, $arr['args']);

                    $stream = (new Stream())->event();

                    if($a['is_ie']){
                        $stream->addComment(str_repeat(" ", 2048)) // 2 kB padding for old IE (8/9)
                    }

                    if(count($sourceData)) === 0){
                        $stream->setRetry(2000);
                                     ->end()
                                        ->flush();
                    }

                        $stream->setId()
                                ->setEvent($a[''])
                                    ->setData($sourceData)
            				            ->end()
            				                ->flush();
                    }

                };
            

            return $this->register_cancellable_shutdown_function($call, $fn, $arr);
        }

        private function register_cancellable_shutdown_function($callback){
                
                return new CancellableShutdownCallback(func_get_args());
        }

        private function stream_work(){

            set_time_limit( 0 );
            $sleep =  $this->source_ops_interval - (time());
            $ordinal = NULL;

            while ( TRUE ){
                 if(time() != $sleep) {
                    if($ordinal){
                        cancel_shutdown_function($ordinal);
                    } 
                   // the looping will pause on the specific time it was set to sleep
                   // it will loop again once it finish sleeping.
                   time_sleep_until($sleep);
                 }

                 #WORK
                 /*echo 'This text will never be seen by the user';*/
                 $ordinal = $this->run_process($this->source_callback, $this->source_callback_args);
            }

        }

}

?>
