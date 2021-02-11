<?php
class Account {
	public $id;
	public $description;
	public $timestamp;

	public function __construct() {
	}
	public function setId($input) {
		$this->id = $input;
	}
	public function getId() {
		return isset ( $this->id ) ? $this->id : NULL;
	}
	public function setDescription($input) {
		$this->description = $input;
	}
	public function getDescription() {
		return isset ( $this->description ) ? $this->description : NULL;
	}
	public function setTimestamp($input) {
		$this->timestamp = $input;
	}
	public function getTimestamp() {
		return isset ( $this->timestamp ) ? $this->timestamp : NULL;
	}
}

