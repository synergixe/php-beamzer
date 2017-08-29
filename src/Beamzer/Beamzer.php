<?php

namespace Beamzer;

use Igorw\EventSource\Stream as Stream;
use Symfony\Component\HttpFoundation\StreamedResponse as StreamedResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Beamzer\Helpers\CancellableShutdownCallback as CancellableShutdownCallback;

class Beamzer {

        private $source_callback;

        private $source_callback_args;

        private $source_ops_interval;

        private $this->last_event_id;

        public static function createStream(array $options){

                return new static($options);
        }

        private function __construct(array $options){

            $this->source_callback = $options['data_source_callback'];
            $this->source_callback_args = $options['data_source_callback_args'];
            $this->source_ops_interval = $options['data_source_ops_interval'];
            $this->last_event_id  = 0;

        }

        public function start(){

            $request = getobject('Illuminate\\Http\\Request');
            
            if($request instanceof Request){
                if($request->hasHeader('Last-Event-ID')){
                    $this->last_event_id = $request->header('Last-Event-ID', 0);
                }
                $this->last_event_id = $request->only(array('lastEventId'));
            }else{
                ;
            }

            $headers = array_merge(Stream::getHeaders(), array('Connection' => 'keep-alive'));

            $this->source_callback_args['lastId'] = $this->last_event_id;

            $response = new StreamedResponse(array(&$this, 'stream_worker'));
	        foreach ($headers as $name => $value) {
            	$response->headers->set($name, $value);
            }
            return $response;

        }

        private function bg_process($fn, $arr){
            if(class_exists('Closure')){
                $call = function($f, $a){
                    /*set_cookie();
                    header('Connection: close');
                    header('Content-length: 0');
                    ob_flush();
                    flush();*/
                    $sourceData = call_user_func_array($f, $a['args']);

                    $stream = (new Stream())->event();

                    if($a['isIE']){
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
            }else{
                $call = create_function('$f,$a', "/*set_cookie();\r\nheader('Connection: close');\r\n");
            }

            return $this->register_cancellable_shutdown_function($call, $fn, $arr);
        }

        private function register_cancellable_shutdown_function($callback){
                
                return new CancellableShutdownCallback(func_get_args());
        }

        private function stream_worker(){

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
                 $ordinal = $this->bg_process($this->source_callback, $this->source_callback_args);
            }

        }

}

?>