<?php

namespace Synergixe\PHPBeamzer\Helpers;

use Igorw\EventSource\EchoHandler;

class BufferBustingEchoHandler extends EchoHandler {

    /**
     * @var string
     */
    private $buffer;
    
    /**
     * @param int $bufferSize
     */
    public function __construct($bufferSize = 4096){
    
        $this->buffer = str_repeat(" ", $bufferSize)."\n";
    }
    
    public function __invoke($chunk){
    
        echo $this->buffer;
        
        parent::__invoke($chunk);
    }
    
}
