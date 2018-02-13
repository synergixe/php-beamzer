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

        public function __construct(array $arguments){

                $this->callback = $arguments[0];

                $that = $this;

                register_shutdown_function(function() use ($that, $arguments) {

                        $details = array_slice($arguments, 1);

                        is_array($that->callback) && call_user_func_array(

                                $that->callback, $details[0]
                        );
                });
        }

        public function cancel(){

                $this->callback = false;
        }
}

?>
