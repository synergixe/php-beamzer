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

namespace Synergixe\PHPBeamzer\Helpers;

final class CancellableShutdownCallback {

        private $callback;

        public function __construct(\array $arguments){
                
                $this->callback = $arguments[0];
                
                register_shutdown_function(function() {

                        $this->callback && call_user_func_array(
                                
                                $this->callback, array_slice($arguments, 1)
                        );
                });
        }

        public function cancel(){

                $this->callback = false;
        }
}

?>