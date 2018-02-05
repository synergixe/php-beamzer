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

use Illuminate\Support\Facades\Facade;

class Streamer extends Facade {

    protected static function getFacadeAccessor(){

        return 'streamer';
    }
}


?>
