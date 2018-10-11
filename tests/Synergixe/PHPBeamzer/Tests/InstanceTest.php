<?php

namespace Synergixe\PHPBeamzer\Test;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;
use Mockery;
use PHPUnit\Framework\TestCase;

class InstanceTest extends TestCase {

	public function setUp(){
		
        	parent::setUp();
    	}
	
    	public function tearDown(){
		
        	Mockery::close();
    	}
	
    	/**
     	 * Check that the multiply method returns correct result
     	 * @return void
     	 */

    	public function test_instance_is_created(){

	      	$beamzer = Beamzer::createInstance(NULL, NULL);

        	$this->assertTrue(method_exists($beamzer, 'send'));
    	}
	
}

?>
