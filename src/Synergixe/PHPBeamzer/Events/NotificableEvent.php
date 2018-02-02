<?php

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
