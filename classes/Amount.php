<?php
/**
 * @package usocrate.prodigimus
 * @author Florent Chanavat
 * @since 12/2020
 */
class Amount {
	public $id;
	public $title;
	public $description;
	public $value;
	public $currency;
	public $source;
	public $source_url;
	
	public function __construct() {
	}

	function __destruct() {
	}
}

