<?php

namespace Beamzer\Helpers;

final class CancellableShutdownCallback {

        private $callback;

        public function __construct(\array $arguments){
                
                $this->callback = $arguments[0];
                
                register_shutdown_function(function() {
                        $this->callback && call_user_func_array(($this->callback, array_slice($arguments, 1));
                });
        }

        public function cancel(){

                $this->callback = false;
        }
}

?>