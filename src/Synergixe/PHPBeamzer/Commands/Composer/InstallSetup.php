<?php

namespace Synergixe\PHPBeamzer\Commands\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class InstallSetup {
    
    public static function preInstall(Event $event) {
        // provides access to the current ComposerIOConsoleIO
        // stream for terminal input/output
        $io = $event->getIO();
        if ($io->askConfirmation("Are you sure you want to install php-beamzer ? ", false)) {
            // ok, continue on to composer install
            return true;
        }
        // exit composer and terminate installation process
        exit;
    }

    public static function postUpdate(Event $event){
    
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postAutoloadDump(Event $event){
    
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        // do stuff
    }

    public static function postPackageInstall(PackageEvent $event){
    
        $installedPackage = $event->getComposer()->getPackage();
        // do stuff
    }

    public static function postInstall(Event $event){
        // provides access to the current Composer instance
        $composer = $event->getComposer();
        // do stuff
    }
    
    // Synergixe\PHPBeamzer\Commands\Composer\InstallSetup::postPackageInstall
}

?>
