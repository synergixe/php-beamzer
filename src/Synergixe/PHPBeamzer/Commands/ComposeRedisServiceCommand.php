<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.6
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ComposeRedisServiceCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redis:compose';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Compose A New Redis Service For Message-Driven Notification";
 
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire(){
      
        $this->line('Welcome to the Redis Service Composer for PHPBeamzer.');
    }
}

?>
