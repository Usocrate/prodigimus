<?php
class AccountingEntry {
	public $id;
	public $account_id;
	public $date;
	public $value_date;
	public $description;
	public $type;
	public $amount;
	public $timestamp;
	public function __construct() {
	}
	public function setId($input) {
		$this->id = $input;
	}
	public function getId() {
		return isset ( $this->id ) ? $this->id : NULL;
	}
	public function setAccountId($input) {
		$this->account_id = $input;
	}
	public function getAccountId() {
		return isset ( $this->account_id ) ? $this->account_id : NULL;
	}
	public function setDate($input) {
		$this->date = new DateTime ( $input );
	}
	/**
	 *
	 * @since 02/2021
	 * @param
	 *        	$input
	 */
	public function setDateFromCsv($input) {
		$matches = array ();
		preg_match ( '#(\d{2})/(\d{2})/(\d{4})#', $input, $matches );
		$this->setDate ( $matches [3] . '-' . $matches [2] . '-' . $matches [1] );
	}
	/**
	 *
	 * @since 02/2021
	 * @param
	 *        	$input
	 */
	public function setValueDateFromCsv($input) {
		$matches = array ();
		preg_match ( '#(\d{2})/(\d{2})/(\d{4})#', $input, $matches );
		$this->setValueDate ( $matches [3] . '-' . $matches [2] . '-' . $matches [1] );
	}
	/**
	 *
	 * @since 02/2021
	 * @param
	 *        	$input
	 */
	public function setAmountAndTypeFromCsv($input) {
		$matches = array ();
		preg_match ( '/(-)?((\d{1,3}|\s)+),(\d{2})/u', $input, $matches );
		// var_dump($matches);
		$a = preg_replace ( '/\s/u', '', $matches [2] ) . '.' . $matches [4];
		$this->setAmount ( $a );
		strcmp ( $matches [1], '-' ) == 0 ? $this->setType ( 'spending' ) : $this->setType ( 'earning' );
	}
	public function getDate() {
		return isset ( $this->date ) ? $this->date : NULL;
	}
	/**
	 *
	 * @since 02/2021
	 * @return string|NULL
	 */
	public function getDateToDisplay() {
		if (isset ( $this->date ) && is_a ( $this->date, 'DateTime' )) {
			$now = new DateTime ();
			if (strcmp ( $now->format ( 'Y' ), $this->date->format ( 'Y' ) ) == 0) {
				return $this->date->format ( 'd M' );
			} else {
				return $this->date->format ( 'd M Y' );
			}
		}
		return NULL;
	}
	public function setValueDate($input) {
		$this->value_date = new DateTime ( $input );
	}
	public function getValueDate() {
		return isset ( $this->value_date ) ? $this->value_date : NULL;
	}
	public function setDescription($input) {
		$this->description = $input;
	}
	public function getDescription() {
		return isset ( $this->description ) ? $this->description : NULL;
	}
	public function getHtmlDescription() {
		return isset ( $this->description ) ? ToolBox::toHtml ( $this->description ) : NULL;
	}
	public function setType($input) {
		$this->type = $input;
	}
	public function getType() {
		return isset ( $this->type ) ? $this->type : NULL;
	}
	public function setAmount($input) {
		$this->amount = $input;
	}
	public function getAmount() {
		return isset ( $this->amount ) ? $this->amount : NULL;
	}
	public function getAmountToDisplay(NumberFormatter $nf = NULL) {
		if (isset ( $this->amount )) {
			if (is_null ( $nf )) {
				$nf = new NumberFormatter ( 'fr_FR', NumberFormatter::CURRENCY );
			}
			return $nf->formatCurrency ( $this->amount, 'EUR' );
		} else {
			return NULL;
		}
	}
	public function setTimestamp($input) {
		$this->timestamp = $input;
	}
	public function getTimestamp() {
		return isset ( $this->timestamp ) ? $this->timestamp : NULL;
	}
	/**
	 *
	 * @since 02/2021
	 * @param array $collection
	 * @return string
	 */
	public static function collectionToHtml(array $collection, string $caption = NULL) {
		global $system;
		$nf = new NumberFormatter ( 'fr_FR', NumberFormatter::CURRENCY );
		$html = '<table class="table table-sm">';
		if (! empty ( $caption )) {
			$html .= '<caption>' . ToolBox::toHtml ( $caption ) . '</caption>';
		}
		$html .= '<thead><tr><th>Désignation</th><th>Montant</th></tr></thead>';
		$html .= '<tbody>';

		foreach ( $collection as $e ) {
			$html .= '<tr>';
			$html .= '<td>';
			$html .= '<small>' . $e->getDateToDisplay () . '</small><br />';
			$html .= '<a href="' . $system->getAccountingEntryAdminUrl ( $e ) . '">' . ToolBox::toHtml ( $e->description ) . '</a>';
			$html .= '</td>';
			$html .= '<td>';
			switch ($e->type) {
				case 'earning' :
					$html .= '<small>Revenu</small><br/>';
					$html .= $e->getAmountToDisplay ( $nf );
					break;
				case 'spending' :
					$html .= '<small>Dépense</small><br/>';
					$html .= $e->getAmountToDisplay ( $nf );
					break;
				default :
					$html .= $e->getAmountToDisplay ( $nf );
			}
			$html .= '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
	/**
	 *
	 * @since 02/2021
	 * @param
	 *        	$date
	 * @return boolean
	 */
	public function isMoreRecent($date) {
		return $this->date > new Datetime ( $date );
	}
}