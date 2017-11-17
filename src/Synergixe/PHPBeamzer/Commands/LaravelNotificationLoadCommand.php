<?php

/**
 * @copyright Copyright (c) 2018 Oparand Ltd - Synergixe
 *
 * @version v0.1.1
 *
 * @author Ifeora Okechukwu (https://twitter.com/isocroft)
 *
 * @license MIT 
 *
 */

namespace Synergixe\PHPBeamzer\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;


class LaravelNotificationLoadCommand extends Command {


	/**
 	 * @var string
	 */

	protected $signature = 'move-files:notification';


	/**
 	 * @var string
	 */

	protected $description = 'Moving the Beamzer Notification Files';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	public function handle(){

		$file = app_path('Notifications/ActivityStream.php');

		if(file_exists($file) && is_readable($file)){
			$this->info('');
		}
	}

}

?>