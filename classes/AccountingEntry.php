<?php
class AccountingEntry {
	public $id;
	public $account_id;
	public $date;
	public $value_date;
	public $description;
	public $tags;
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
	 */
	public function setDateFromCsv($input) {
		$matches = array ();
		preg_match ( '#(\d{2})/(\d{2})/(\d{4})#', $input, $matches );
		$this->setDate ( $matches [3] . '-' . $matches [2] . '-' . $matches [1] );
	}
	/**
	 *
	 * @since 02/2021
	 */
	public function setValueDateFromCsv($input) {
		$matches = array ();
		preg_match ( '#(\d{2})/(\d{2})/(\d{4})#', $input, $matches );
		$this->setValueDate ( $matches [3] . '-' . $matches [2] . '-' . $matches [1] );
	}
	/**
	 *
	 * @since 02/2021
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
	/**
	 *
	 * @since 09/2021
	 * @return string|NULL
	 */
	public function getMonthToDisplay() {
		$translation = array (
				'01' => 'janvier',
				'02' => 'février',
				'03' => 'mars',
				'04' => 'avril',
				'05' => 'mai',
				'06' => 'juin',
				'07' => 'juillet',
				'08' => 'août',
				'09' => 'septembre',
				'10' => 'octobre',
				'11' => 'novembre',
				'12' => 'décembre'
		);
		//var_dump ( $translation );
		if (isset ( $this->date ) && is_a ( $this->date, 'DateTime' )) {
			$now = new DateTime ();
			$index = $this->date->format ( 'm' );
			//echo 'index:' . $index;
			$year = $this->date->format ( 'Y' );
			if (strcmp ( $now->format ( 'Y' ), $year ) != 0) {
				return isset ( $translation [$index] ) ? ucfirst( $translation [$index] ) . ' ' . $year : $this->date->format ( 'm / Y' );
			} else {
				return isset ( $translation [$index] ) ? ucfirst ( $translation [$index] ) : $this->date->format ( 'm' );
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
	public function setTags($input) {
		if (isset ( $input ))
			$this->tags = explode ( ',', $input );
	}
	public function getTags() {
		return isset ( $this->tags ) ? $this->tags : NULL;
	}
	public function getCommaSeparatedTags() {
		return isset ( $this->tags ) ? $this->tags : NULL;
	}
	public function getHtmlTags() {
		global $system;

		if (isset ( $this->tags )) {
			$output = '';
			foreach ( $this->tags as $t ) {
				$output .= '<a href="' . $system->getAppliUrl () . '/admin/tag.php?label=' . urlencode ( $t ) . '"><span class="badge badge bg-light text-dark">' . ToolBox::toHtml ( $t ) . '</span></a> ';
			}
			return $output;
		}
	}
	public function isTagged() {
		return isset($this->tags) && is_array($this->tags) && count($this->tags)>0;
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
	public static function collectionToHtml(array $collection) {
		global $system;

		$html = '';

		foreach ( $collection as $e ) {

			$date = $e->getDate ();
			$month = $date->format ( 'M Y' );
			// var_dump($month);

			if (! isset ( $lastDisplayedMonth ) || strcmp ( $month, $lastDisplayedMonth ) != 0) {
				if (isset ( $lastDisplayedMonth )) {
					$html .= '</ul>';
				}
				$html .= '<h3 class="mt-3">' . $e->getMonthToDisplay () . '</h3>';
				$html .= '<ul class="list-group">';
				$lastDisplayedMonth = $month;
			}

			$html .= '<li class="list-group-item">';
			
			$html .= '<div class="d-flex w-100 justify-content-between">';
			
			$html .= '<div>';
			$html .= '<small>'.$date->format ( 'd' ) . ' ' . $e->getMonthToDisplay ().'</small></br>';
			$html .= '<h4><a href="' . $system->getAccountingEntryAdminUrl ( $e ) . '">' . ToolBox::toHtml ( $e->description ) . '</a></h4>';
			
			if ($e->isTagged()) {
				$html .= '<div>' . $e->getHtmlTags () . '</div>';
			} else {
				if (strcmp($e->type, 'spending')==0) {
					$html .= '<div><a href="accounting_entry_tag.php?id='.$e->getId().'" class="btn btn-outline-secondary btn-sm mt-1">Catégoriser</a></div>';
				}
			}
			$html .= '</div>';
						
			$html .= '<div>';
			switch ($e->type) {
				case 'earning' :
					$html .= '<small>Revenu</small><br/>';
					$html .= $e->getAmountToDisplay ();
					break;
				case 'spending' :
					$html .= '<small>Dépense</small><br/>';
					$html .= $e->getAmountToDisplay ();
					break;
				default :
					$html .= $e->getAmountToDisplay ();
			}
			$html .= '</div>';

			$html .= '</div>';
			
			$html .= '</li>';
		}
		return $html;
	}
	/**
	 *
	 * @since 02/2021
	 * @return boolean
	 */
	public function isMoreRecent($date) {
		return $this->date > new Datetime ( $date );
	}
}