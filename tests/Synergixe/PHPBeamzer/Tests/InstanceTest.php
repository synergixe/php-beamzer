<?php

namespace Synergixe\PHPBeamzer\Test;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;

class InstanceTest extends SynergixeTestCase {

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
