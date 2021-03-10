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
	public $type;
	public $source;
	public $source_url;
	
	public function __construct() {
	}

	function __destruct() {
	}
	
	public function setId($input) {
		$this->id = $input;
	}
	
	public function getId() {
		return isset($this->id) ? $this->id : NULL;
	}
	
	public function getTitle() {
		return isset($this->title) ? $this->title : NULL;
	}
	
	public function setTitle($input) {
		$this->title = $input;
	}
	
	public function getDescription() {
		return isset($this->description) ? $this->description : NULL;
	}
	
	public function setDescription($input) {
		$this->description = $input;
	}
	
	public function getSource() {
		return isset($this->source) ? $this->source : NULL;
	}
	
	public function setSource($input) {
		$this->source = $input;
	}
	
	public function getSourceUrl() {
		return isset($this->source_url) ? $this->source_url : NULL;
	}
	
	public function setSourceUrl($input) {
		$this->source_url = $input;
	}
	
	public function isSourceUrlKnown() {
		return isset($this->source_url);
	}
	
	public function setValue($input) {
		$this->value = $input;
	}
	
	
	public function getValueToDisplay(NumberFormatter $nf = NULL) {
		if (isset($this->value)) {
			if (is_null($nf)) {
				$nf = New NumberFormatter('fr_FR', NumberFormatter::CURRENCY);
			}
			return $nf->formatCurrency($this->value, 'EUR');
		} else {
			return NULL;
		}
	}
	
	public function setType($input) {
		$this->type = $input;
	}
	
}

