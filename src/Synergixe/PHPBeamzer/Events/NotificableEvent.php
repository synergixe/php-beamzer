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

	public $reciever = NULL;

	/**
	 * @var int
	 */

	public $timing = -1;
	
	/**
	 * @var mixed
	 */
	
	private $kind = NULL;

	public function __construct(Model $producer, Model $reciever){

		$this->producer = $producer;

		$this->reciever = $reciever;

		$this->timing = time();
	}
	
	public function getKind(){
	
		return $this->kind;
	}
	
	public function setKind($kind){
	
		$this->kind = $kind;
	}

	public function broadcastOn(){

		return [];
	}
}
?>
