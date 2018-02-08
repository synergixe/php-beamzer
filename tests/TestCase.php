<?php

namespace Synergixe\PHPBeamzer\Test;

use Synergixe\PHPBeamzer\Facades\Laravel\Streamer;
use synergixe\PHPBeamzer\Providers\Laravel\BeamzerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase{



    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return Synergixe\PHPBeamzer\BeamzerServiceProvider
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
