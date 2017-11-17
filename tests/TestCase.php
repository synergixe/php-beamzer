<?php

namespace Synergixe\PHPBeamzer\Test;
use Synergixe\PHPBeamzer\BeamzerFacade;
use synergixe\PHPBeamzer\BeamzerServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase{



    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return Synergixe\Beamzer\BeamzerServiceProvider
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
            'Beamzer' => BeamzerFacade::class,
        ];
    }
}