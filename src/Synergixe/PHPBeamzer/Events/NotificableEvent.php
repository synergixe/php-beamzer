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

namespace Synergixe\PHPBeamzer\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model as Model;

class NotificableEvent {

	 use SerializesModels;

	/**
	 * @var App\User
	 */
	public $producer = NULL;

	/**
	 * @var Eloquent\Model
	 */

	public $payload = NULL;

	/**
	 * @var int
	 */

	public $timing = -1;

	public function __construct(Model $producer, Model $payload){

		$this->producer = $producer;

		$this->payload = $payload;

		$this->timing = time();
	}

	public function broadcastOn(){

		return [];
	}
}
?>
