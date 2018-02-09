<?php

namespace Synergixe\PHPBeamzer\Commands\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InstallSetup {

    public static function postUpdate(Event $event){
    
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postAutoloadDump(Event $event){
    
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        // do stuff
    }

    public static function postPackageInstall(PackageEvent $event){
    
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function postInstall(Event $event){
    
        // do stuff
    }
}

?>
