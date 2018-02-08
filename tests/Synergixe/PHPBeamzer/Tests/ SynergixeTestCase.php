<?php

namespace Synergixe\PHPBeamzer\Test;

use Synergixe\PHPBeamzer\Facades\Laravel\Streamer;
use Synergixe\PHPBeamzer\Providers\Laravel\BeamzerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class  SynergixeTestCase extends OrchestraTestCase{
  
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return Synergixe\PHPBeamzer\Providers\Laravel\BeamzerServiceProvider
     */
  
    protected function getPackageProviders($app){
      
        return [BeamzerServiceProvider::class];
      
    }
    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app){
      
        return [
            'Streamer' => Streamer::class,
        ];
      
    }
    
    /*public function mock($class){
        
        $mock = Mockery::mock($class);
        
        $this->app->instance($class, $mock);
        
        return $mock;
        
    }*/
}

?>
