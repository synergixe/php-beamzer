<?php

namespace Synergixe\PHPBeamzer\Test;

use Synergixe\PHPBeamzer\Beamzer as Beamzer;

class InstanceTest extends TestCase {

    /**
     * Check that the multiply method returns correct result
     * @return void
     */

    public function testInstanceIsCreated(){

	$beamzer = Beamzer::createStream();

        $this->assertTrue($beamzer instanceOf Beamzer);
    }
}