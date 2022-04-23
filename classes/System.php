<?php
/**
 * @package usocrate.prodigimus
 * @author Florent Chanavat
 */
// namespace classes;
class System {
	private $db_host;
	private $db_name;
	private $db_user;
	private $db_password;
	private $pdo;
	private $appli_name;
	private $appli_description;
	private $appli_url;
	private $appli_theme_color;
	private $appli_background_color;
	private $dir_path;
	public function __construct($path) {
		$this->config_file_path = $path;
		if ($this->configFileExists ()) {
			$this->parseConfigFile ();
		}
	}
	public function setDbHost($input) {
		$this->db_host = $input;
	}
	public function getDbHost() {
		return $this->db_host;
	}
	public function setDbName($input) {
		$this->db_name = $input;
	}
	public function getDbName() {
		return $this->db_name;
	}
	public function setDbUser($input) {
		$this->db_user = $input;
	}
	public function getDbUser() {
		return $this->db_user;
	}
	public function setDbPassword($input) {
		$this->db_password = $input;
	}
	public function getDbPassword() {
		return $this->db_password;
	}
	public function setAppliName($input) {
		$this->appli_name = $input;
	}
	public function getAppliName() {
		return $this->appli_name;
	}
	public function setAppliDescription($input) {
		$this->appli_description = $input;
	}
	public function getAppliDescription() {
		return $this->appli_description;
	}
	public function setAppliUrl($input) {
		$this->appli_url = $input;
	}
	public function getAppliUrl() {
		return $this->appli_url;
	}
	public function setAppliThemeColor($input) {
		$this->appli_theme_color = $input;
	}
	public function getAppliThemeColor() {
		return $this->appli_theme_color;
	}
	public function setAppliBackgroundColor($input) {
		$this->appli_background_color = $input;
	}
	public function getAppliBackgroundColor() {
		return $this->appli_background_color;
	}
	public function getSkinUrl() {
		return $this->appli_url . '/skin';
	}
	public function getImagesUrl() {
		return $this->getSkinUrl () . '/images';
	}
	public function setDirPath($input) {
		$this->dir_path = $input;
	}
	public function getDirPath() {
		return $this->dir_path;
	}
	public function getClassDirPath() {
		return $this->dir_path . DIRECTORY_SEPARATOR . 'classes';
	}
	/**
	 *
	 * @version 03/2017
	 */
	public function getDataDirPath() {
		$path = $this->dir_path . DIRECTORY_SEPARATOR . 'data';
		if (! is_dir ( $path )) {
			mkdir ( $path, 770 );
		}
		return $path;
	}
	/**
	 *
	 * @since 01/2021
	 * @return string
	 */
	public function getUploadDirectoryPath() {
		try {
			$path = $this->getDataDirPath () . DIRECTORY_SEPARATOR . 'upload';
			if (! is_dir ( $path )) {
				mkdir ( $path, 0770 );
			}
			return $path;
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
		}
	}
	public function getUploadMaxFileSize() {
		try {
			$input = strtolower ( ini_get ( 'upload_max_filesize' ) );
			switch (substr ( $input, - 1 )) {
				case 'm' :
					return ( int ) $input * 1048576;
				case 'k' :
					return ( int ) $input * 1024;
				case 'g' :
					return ( int ) $input * 1073741824;
				default :
					throw new Exception ( 'La taille maximum des fichiers pouvant être téléchargés ne peut être calculée' );
			}
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
		}
	}
	/**
	 *
	 * @since 01/2021
	 * @return string
	 */
	public function getManifestPath() {
		return $this->dir_path . DIRECTORY_SEPARATOR . 'skin' . DIRECTORY_SEPARATOR . 'manifest.json';
	}
	/**
	 *
	 * @since 01/2021
	 * @return string
	 */
	public function getManifestUrl() {
		return $this->getSkinUrl () . '/manifest.json';
	}
	/**
	 *
	 * @since 02/2021
	 * @param AccountingEntry $ae
	 * @return string
	 */
	public function getAccountingEntryAdminUrl(AccountingEntry $ae) {
		$url = $this->getAppliUrl () . '/admin/accounting_entry.php';
		$url .= '?id=' . urlencode ( $ae->getId () );
		return $url;
	}
	/**
	 *
	 * @since 10/2016
	 * @return boolean
	 */
	public function configFileExists() {
		return file_exists ( $this->config_file_path );
	}
	/**
	 *
	 * @since 01/2021
	 * @return boolean
	 */
	public function manifestFileExists() {
		return file_exists ( $this->getManifestPath () );
	}
	/**
	 *
	 * @since 10/2016
	 * @return boolean
	 */
	public function parseConfigFile() {
		try {
			if (is_readable ( $this->config_file_path )) {
				$data = json_decode ( file_get_contents ( $this->config_file_path ), true );
				foreach ( $data as $key => $value ) {
					// echo $key.': '.$value.'<br>';
					switch ($key) {
						case 'db_host' :
							$this->db_host = $value;
							break;
						case 'db_name' :
							$this->db_name = $value;
							break;
						case 'db_user' :
							$this->db_user = $value;
							break;
						case 'db_password' :
							$this->db_password = $value;
							break;
						case 'appli_name' :
							$this->appli_name = $value;
							break;
						case 'appli_description' :
							$this->appli_description = $value;
							break;
						case 'appli_url' :
							$this->appli_url = $value;
							break;
						case 'appli_theme_color' :
							$this->appli_theme_color = $value;
							break;
						case 'appli_background_color' :
							$this->appli_background_color = $value;
							break;
						case 'dir_path' :
							$this->dir_path = $value;
							break;
					}
				}
			} else {
				throw new Exception ( 'Le fichier de configuration doit être accessible en lecture.' );
			}
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
			return false;
		}
	}
	/**
	 *
	 * @since 10/2016
	 * @return number|boolean
	 */
	public function saveConfigFile() {
		try {
			$a = array (
					'db_host' => $this->db_host,
					'db_name' => $this->db_name,
					'db_user' => $this->db_user,
					'db_password' => $this->db_password,
					'appli_name' => $this->appli_name,
					'appli_description' => $this->appli_description,
					'appli_url' => $this->appli_url,
					'appli_theme_color' => $this->appli_theme_color,
					'appli_background_color' => $this->appli_background_color,
					'dir_path' => $this->dir_path
			);
			return file_put_contents ( $this->config_file_path, json_encode ( $a ) );
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
			return false;
		}
	}
	/**
	 *
	 * @since 01/2021
	 * @return number|boolean
	 */
	public function saveManifestFile() {
		try {
			$icons = array ();
			$sizes = array (
					'192x192',
					'256x256',
					'512x512'
			);
			foreach ( $sizes as $s ) {
				$i = new Icon ();
				$i->src = $this->getImagesUrl () . '/android-chrome-' . $s . '.png';
				$i->sizes = $s;
				$i->type = 'image/png';
				$icons [] = clone $i;
			}
			$a = array (
					"name" => $this->appli_name,
					"short_name" => $this->appli_name,
					"icons" => $icons,
					"theme_color" => $this->appli_theme_color,
					"background_color" => $this->appli_background_color,
					"display" => "standalone"
			);
			return file_put_contents ( $this->getManifestPath (), json_encode ( $a ) );
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
			return false;
		}
	}

	/**
	 * Retourne un PHP Data Object permettant de se connecter à la date de données.
	 *
	 * @since 08/2014
	 * @return PDO
	 */
	public function getPdo() {
		try {
			if (! isset ( $this->pdo )) {
				$this->pdo = new PDO ( 'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_user, $this->db_password, array (
						PDO::ATTR_PERSISTENT => true
				) );
				$this->pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				$this->pdo->exec ( 'SET NAMES utf8' );
			}
			return $this->pdo;
		} catch ( PDOException $e ) {
			switch ($e->getCode ()) {
				case 1049 :
					if ($this->createDatabase ()) {
						return $this->getPdo ();
					}
					return false;
				default :
					$this->reportException ( $e );
					return false;
			}
		}
	}
	/**
	 *
	 * @since 12/2020
	 * @version 02/2021
	 * @return PDO|boolean
	 */
	public function createDatabase() {
		try {
			$pdo = new PDO ( 'mysql:host=' . $this->db_host, $this->db_user, $this->db_password );
			$pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$pdo->beginTransaction ();

			$pdo->exec ( 'CREATE DATABASE IF NOT EXISTS `' . $this->db_name . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' );
			$pdo->exec ( 'USE  `' . $this->db_name . '` ' );

			$pdo->exec ( "CREATE TABLE IF NOT EXISTS `amount` (`id` SMALLINT(5) unsigned NOT NULL AUTO_INCREMENT,`title` TINYTEXT NOT NULL,`description` TEXT,`value` DECIMAL(17,2),`currency` CHAR(3) NOT NULL DEFAULT 'EUR',`type` ENUM('spending', 'earning', 'giving', 'fighting', 'losing', 'illin''') NOT NULL DEFAULT 'spending',`source` TINYTEXT,`source_url` TINYTEXT,`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			$pdo->exec ( "CREATE TABLE IF NOT EXISTS `account` (`id` SMALLINT(5) unsigned NOT NULL AUTO_INCREMENT,`description` TINYTEXT,`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			$pdo->exec ( "CREATE TABLE IF NOT EXISTS `accounting_entry` (`id` SMALLINT(5) unsigned NOT NULL AUTO_INCREMENT,`account_id` SMALLINT(5) unsigned NOT NULL,`date` DATE NOT NULL,`value_date` DATE NOT NULL,`description` TINYTEXT NOT NULL,`type` ENUM('spending', 'earning', 'giving', 'fighting', 'losing', 'illin''') NOT NULL DEFAULT 'spending',`amount` DECIMAL(14,2) NOT NULL,`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`),KEY (`account_id`),KEY (`type`),CONSTRAINT `ae_fk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			$pdo->exec ( "CREATE TABLE IF NOT EXISTS `tag` (`id` SMALLINT(5) unsigned NOT NULL AUTO_INCREMENT, `label` TINYTEXT NOT NULL, `accounting_entry_id` SMALLINT(5) unsigned NOT NULL, `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY (`accounting_entry_id`), CONSTRAINT `t_fk_1` FOREIGN KEY (`accounting_entry_id`) REFERENCES `accounting_entry` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			// $pdo->exec ("CREATE TABLE IF NOT EXISTS `user` (`id` TINYINT(5) unsigned NOT NULL AUTO_INCREMENT,`name` TINYTEXT NOT NULL,`password` TINYTEXT NOT NULL,`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

			return $pdo->commit ();
		} catch ( PDOException $e ) {
			if ($pdo->inTransaction ()) {
				$pdo->rollBack ();
			}
			$this->reportException ( $e );
			return false;
		}
	}
	/**
	 *
	 * @version 08/2014
	 * @return string
	 */
	public function getHtmlLink() {
		return '<a href="' . $this->getAppliUrl () . '">' . ToolBox::toHtml ( $this->getAppliName () ) . '</a>';
	}
	public function getHtmlHeadTagsForFavicon() {
		$output = array ();
		$output [] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->getSkinUrl () . '/images/favicon-32x32.png">';
		$output [] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $this->getSkinUrl () . '/images/favicon-16x16.png">';
		$output [] = '<link rel="manifest" href="' . $this->getSkinUrl () . '/manifest.json">';
		$output [] = '<meta name="application-name" content="' . ToolBox::toHtml ( $this->getAppliName () ) . '">';
		return $output;
	}
	public function writeHtmlHeadTagsForFavicon() {
		foreach ( $this->getHtmlHeadTagsForFavicon () as $tag ) {
			echo $tag;
		}
	}
	/**
	 *
	 * @since 08/2014
	 * @version 07/2018
	 */
	public function reportException(Exception $e, $comment = null) {
		$toDisplay = $e->getMessage ();
		if (! empty ( $comment )) {
			$toDisplay .= ' (' . $comment . ')';
		}
		echo '<p>' . ToolBox::toHtml ( $toDisplay ) . '</p>';
		// error_log( $toDisplay );
	}
	/**
	 * Enregistre les données d'un objet dans la base de données.
	 *
	 * @version 01/2021
	 * @since 12/2020
	 * @return boolean
	 */
	public function put($o) {
		try {
			switch (get_class ( $o )) {

				case 'Account' :

					$new = empty ( $o->id );

					$settings = array ();

					if (isset ( $o->description )) {
						$settings [] = 'description=:description';
					}

					if (isset ( $o->timestamp )) {
						$settings [] = 'timestamp=:timestamp';
					}

					$sql = $new ? 'INSERT INTO' : 'UPDATE';
					$sql .= ' account SET ';
					$sql .= implode ( ', ', $settings );
					if (! $new) {
						$sql .= ' WHERE id=:id';
					}

					$statement = $this->getPdo ()->prepare ( $sql );

					if (isset ( $o->description )) {
						$statement->bindValue ( ':description', $o->description, PDO::PARAM_STR );
					}

					if (isset ( $o->timestamp )) {
						$statement->bindValue ( ':timestamp', $o->timestamp, PDO::PARAM_STR );
					}

					if (! $new) {
						$statement->bindValue ( ':id', $o->id, PDO::PARAM_INT );
					}

					$result = $statement->execute ();

					if ($result && $new) {
						$o->id = $this->getPdo ()->lastInsertId ();
					}

					return $result;

				case 'AccountingEntry' :

					// var_dump($o);

					$new = empty ( $o->id );

					$settings = array ();
					if (isset ( $o->account_id )) {
						$settings [] = 'account_id=:account_id';
					}
					if (isset ( $o->date )) {
						$settings [] = 'date=:date';
					}
					if (isset ( $o->value_date )) {
						$settings [] = 'value_date=:value_date';
					}
					if (isset ( $o->description )) {
						$settings [] = 'description=:description';
					}
					if (isset ( $o->type )) {
						$settings [] = 'type=:type';
					}
					if (isset ( $o->amount )) {
						$settings [] = 'amount=:amount';
					}

					$sql = $new ? 'INSERT INTO' : 'UPDATE';
					$sql .= ' accounting_entry SET ';
					$sql .= implode ( ', ', $settings );
					if (! $new) {
						$sql .= ' WHERE id=:id';
					}

					$statement = $this->getPdo ()->prepare ( $sql );

					if (isset ( $o->account_id )) {
						$statement->bindValue ( ':account_id', $o->account_id, PDO::PARAM_STR );
					}
					if (isset ( $o->date )) {
						$statement->bindValue ( ':date', $o->date->format ( 'Y/m/d' ), PDO::PARAM_STR );
					}
					if (isset ( $o->value_date )) {
						$statement->bindValue ( ':value_date', $o->value_date->format ( 'Y/m/d' ), PDO::PARAM_STR );
					}
					if (isset ( $o->description )) {
						$statement->bindValue ( ':description', $o->description, PDO::PARAM_STR );
					}
					if (isset ( $o->type )) {
						$statement->bindValue ( ':type', $o->type, PDO::PARAM_STR );
					}
					if (isset ( $o->amount )) {
						$statement->bindValue ( ':amount', $o->amount, PDO::PARAM_STR );
					}

					if (! $new) {
						$statement->bindValue ( ':id', $o->id, PDO::PARAM_INT );
					}

					$result = $statement->execute ();

					if ($result && $new) {
						$o->id = $this->getPdo ()->lastInsertId ();
					}

					return $result;

				case 'Amount' :

					$new = empty ( $o->id );

					$settings = array ();
					if (isset ( $o->title )) {
						$settings [] = 'title=:title';
					}
					if (isset ( $o->description )) {
						$settings [] = 'description=:description';
					}
					if (isset ( $o->value )) {
						$settings [] = 'value=:value';
					}
					if (isset ( $o->source )) {
						$settings [] = 'source=:source';
					}
					if (isset ( $o->source_url )) {
						$settings [] = 'source_url=:source_url';
					}

					$sql = $new ? 'INSERT INTO' : 'UPDATE';
					$sql .= ' amount SET ';
					$sql .= implode ( ', ', $settings );
					if (! $new) {
						$sql .= ' WHERE id=:id';
					}

					$statement = $this->getPdo ()->prepare ( $sql );

					if (isset ( $o->title )) {
						$statement->bindValue ( ':title', $o->title, PDO::PARAM_STR );
					}
					if (isset ( $o->description )) {
						$statement->bindValue ( ':description', $o->description, PDO::PARAM_STR );
					}
					if (isset ( $o->value )) {
						$statement->bindValue ( ':value', $o->value, PDO::PARAM_STR );
					}
					if (isset ( $o->source )) {
						$statement->bindValue ( ':source', $o->source, PDO::PARAM_STR );
					}
					if (isset ( $o->source_url )) {
						$statement->bindValue ( ':source_url', $o->source_url, PDO::PARAM_STR );
					}

					if (! $new) {
						$statement->bindValue ( ':id', $o->id, PDO::PARAM_INT );
					}

					$result = $statement->execute ();

					if ($result && $new) {
						$o->id = $this->getPdo ()->lastInsertId ();
					}

					return $result;
			}
			return false;
		} catch ( Exception $e ) {
			var_dump ( $o );
			$this->reportException ( $e );
		}
	}
	/**
	 *
	 * @since 01/2021
	 * @return Account|NULL
	 */
	public function getAccount($id) {
		$sql = 'SELECT * FROM account WHERE id=:id';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':id', $id, PDO::PARAM_INT );
		$statement->execute ();
		$data = $statement->fetch ( PDO::FETCH_ASSOC );
		if ($data) {
			$output = new Account ();
			$output->id = $data ['id'];
			$output->description = $data ['description'];
			$output->timestamp = $data ['timestamp'];
			return $output;
		}
		return null;
	}
	public function getAccountingEntry($id) {
		$sql = 'SELECT * FROM accounting_entry WHERE id=:id';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':id', $id, PDO::PARAM_INT );
		$statement->execute ();
		$data = $statement->fetch ( PDO::FETCH_ASSOC );
		if ($data) {
			$output = new AccountingEntry ();
			$output->setId ( $data ['id'] );
			$output->setAccountId ( $data ['account_id'] );
			$output->setDate ( $data ['date'] );
			$output->setValueDate ( $data ['value_date'] );
			$output->setDescription ( $data ['description'] );
			$output->setType ( $data ['type'] );
			$output->setAmount ( $data ['amount'] );
			$output->setTimestamp ( $data ['timestamp'] );
			return $output;
		}
		return null;
	}
	/**
	 *
	 * @since 01/2021
	 * @return Account[]
	 */
	public function getAccounts() {
		$sql = 'SELECT * FROM account ORDER BY timestamp DESC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->execute ();
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		$output = array ();
		foreach ( $rows as $r ) {
			$a = new Account ();
			$a->id = $r ['id'];
			$a->description = $r ['description'];
			$a->timestamp = $r ['timestamp'];
			$output [] = clone $a;
			unset ( $a );
		}
		return $output;
	}
	/**
	 *
	 * @since 01/2021
	 * @version 04/2022
	 * @param Account $account
	 * @return AccountingEntry[]
	 */
	public function getAccountingEntries(Account $account, $criteria=NULL) {
		$sql = 'SELECT e.*, GROUP_CONCAT(t.label ORDER BY t.label ASC SEPARATOR \',\') AS tags';
		$sql .= ' FROM accounting_entry AS e LEFT OUTER JOIN tag AS t ON (e.id = t.accounting_entry_id)';
		
		// WHERE
		$where = array ();
		$where[] = 'e.account_id=:account_id';
		
		if (isset($criteria['descriptionSubstr'])) {
			$where[] = 'e.description LIKE :description';
		}
		
		if (isset($criteria['tagLessSpendingOnly'])) {
			if ($criteria['tagLessSpendingOnly']===true) {
				$where[] = "type = :type";
			}
		}

		if (count ( $where ) > 0) {
			$sql .= ' WHERE ' . implode ( ' AND ', $where );
		}
		
		$sql .= ' GROUP BY e.id';
		
		$having = array ();
		
		if (isset($criteria['tagLessSpendingOnly'])) {
			if ($criteria['tagLessSpendingOnly']===true) {
				$having[] = 'tags IS NULL';
			}
		}
		
		if (count ( $having ) > 0) {
			$sql .= ' HAVING ' . implode ( ' AND ', $having );
		}
		
		$sql .= ' ORDER BY e.date DESC';
		
		$statement = $this->getPdo ()->prepare ( $sql );
		
		$statement->bindValue ( ':account_id', $account->getId (), PDO::PARAM_INT );
		
		if (isset($criteria['descriptionSubstr'])) {
			$statement->bindValue ( ':description', '%'.$criteria['descriptionSubstr'].'%', PDO::PARAM_STR);
		}
		
		if (isset($criteria['tagLessSpendingOnly'])) {
			if ($criteria['tagLessSpendingOnly']===true) {
				$statement->bindValue ( ':type', 'spending', PDO::PARAM_STR);
			}
		}
		
		$statement->execute ();
		//echo $statement->debugDumpParams();
		
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		$output = array ();
		foreach ( $rows as $r ) {
			$e = new AccountingEntry ();
			$e->setId ( $r ['id'] );
			$e->setDate ( $r ['date'] );
			$e->setValueDate ( $r ['value_date'] );
			$e->setDescription ( $r ['description'] );
			$e->setTags ( $r ['tags'] );
			$e->setAmount ( $r ['amount'] );
			$e->setType ( $r ['type'] );
			$e->setTimestamp ( $r ['timestamp'] );
			$output [] = clone $e;
			unset ( $e );
		}
		return $output;
	}
	/**
	 * @since 12/2021
	 * @param AccountingEntry $ae
	 * @return string|NULL
	 */
	public function getSimilarityClueToSearchInDescription(AccountingEntry $ae) {
		if (preg_match ( '/(PAIEMENT (CB|PSC) [0-9]{4}) (.+) (CARTE [0-9]{8})/', $ae->getDescription (), $matches )) {
			// Paiement par carte bancaire
			// ex. : PAIEMENT CB 0302 TASSIN LA DEM AUCHAN SUPER MA CARTE 34500495
			// print_r($matches);
			return '%' . $matches [3] . '%';
		} else {
			if (preg_match( '/PRLV SEPA ([ |[A-Z]+]*)/', $ae->getDescription (), $matches )) {
				// Prélèvement SEPA
				// ex. : PRLV SEPA FREE TELECOM FHD 995033022 FREE HAUTDEBIT 995033022
				//print_r($matches);
				return '%' . $matches [1] . '%';
			} else {
				return $ae->getDescription ();
			}
		}
	}
	/**
	 * @since 02/2021
	 * @param AccountingEntry $ae
	 * @return AccountingEntry[]
	 */
	public function getSimilarAccountingEntries(AccountingEntry $ae) {
		$sql = 'SELECT e.*, GROUP_CONCAT(t.label ORDER BY t.label ASC SEPARATOR \',\') AS tags FROM accounting_entry AS e';
		$sql .= ' LEFT OUTER JOIN tag AS t ON (e.id = t.accounting_entry_id)';
		$sql .= ' WHERE e.id!=:id AND e.description LIKE :description';
		$sql .= ' GROUP BY e.id ORDER BY e.date DESC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':id', $ae->getId (), PDO::PARAM_INT );
		$statement->bindValue ( ':description', $this->getSimilarityClueToSearchInDescription($ae), PDO::PARAM_STR );
		$statement->execute ();
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		// $statement->debugDumpParams();

		$output = array ();
		foreach ( $rows as $r ) {
			$e = new AccountingEntry ();
			$e->setId ( $r ['id'] );
			$e->setDate ( $r ['date'] );
			$e->setValueDate ( $r ['value_date'] );
			$e->setDescription ( $r ['description'] );
			$e->setTags ( $r ['tags'] );
			$e->setAmount ( $r ['amount'] );
			$e->setType ( $r ['type'] );
			$e->setTimestamp ( $r ['timestamp'] );
			$output [] = clone $e;
			unset ( $e );
		}
		return $output;
	}
	/**
	 * @since 12/2021
	 * @param AccountingEntry $ae
	 * @return array
	 */
	public function getSimilarAccountingEntriesTags(AccountingEntry $ae) {
		$sql = 'SELECT DISTINCT(t.label) FROM accounting_entry AS e';
		$sql .= ' INNER JOIN tag AS t ON (t.accounting_entry_id = e.id)';
		$sql .= ' WHERE e.id!=:id AND e.description LIKE :description';
		$sql .= ' ORDER BY t.label ASC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':id', $ae->getId (), PDO::PARAM_INT );
		$statement->bindValue ( ':description', $this->getSimilarityClueToSearchInDescription($ae), PDO::PARAM_STR );
		$statement->execute ();
		return $statement->fetchAll (PDO::FETCH_COLUMN);
	}
	public function getLastAccountingEntryDate(Account $account) {
		$sql = 'SELECT date FROM accounting_entry WHERE account_id=:account_id ORDER BY date DESC LIMIT 1';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':account_id', $account->getId (), PDO::PARAM_INT );
		$statement->execute ();
		$data = $statement->fetchColumn ();
		return empty ( $data ) ? null : new DateTime ( $data );
	}
	/**
	 *
	 * @since 12/2020
	 * @param int $id
	 * @return Amount|NULL
	 */
	public function getAmount($id) {
		$sql = 'SELECT * FROM amount WHERE id=:id';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':id', $id, PDO::PARAM_INT );
		$statement->execute ();
		$data = $statement->fetch ( PDO::FETCH_ASSOC );
		if ($data) {
			$output = new Amount ();
			$output->setId ( $data ['id'] );
			$output->setTitle ( $data ['title'] );
			$output->setDescription ( $data ['description'] );
			$output->setValue ( $data ['value'] );
			$output->setSource ( $data ['source'] );
			$output->setSourceUrl ( $data ['source_url'] );
			return $output;
		}
		return null;
	}
	/**
	 *
	 * @since 12/2020
	 * @return Amount[]
	 */
	public function getAmounts() {
		$sql = 'SELECT * FROM amount ORDER BY value DESC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->execute ();
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		$output = array ();
		foreach ( $rows as $r ) {
			$a = new Amount ();
			$a->setId ( $r ['id'] );
			$a->setTitle ( $r ['title'] );
			$a->setDescription ( $r ['description'] );
			$a->setValue ( $r ['value'] );
			$a->setType ( $r ['type'] );
			$a->setSource ( $r ['source'] );
			$a->setSourceUrl ( $r ['source_url'] );
			$output [] = clone $a;
			unset ( $a );
		}
		return $output;
	}
	/**
	 *
	 * @since 02/2021
	 * @param AccountingEntry $ae
	 * @return array
	 */
	public function getAccountingEntryTags(AccountingEntry $ae) {
		$sql = 'SELECT DISTINCT(label) FROM tag WHERE accounting_entry_id=:ae_id ORDER BY label ASC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':ae_id', $ae->getId (), PDO::PARAM_INT );
		$statement->execute ();
		return $statement->fetchAll ( PDO::FETCH_COLUMN );
	}
	/**
	 *
	 * @since 02/2021
	 * @param AccountingEntry $ae
	 * @param string $label
	 * @return boolean
	 */
	public function tagAccountingEntry(AccountingEntry $ae, $label) {
		$sql = 'INSERT INTO tag SET accounting_entry_id=:ae_id, label=:label';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':ae_id', $ae->getId (), PDO::PARAM_INT );
		$statement->bindValue ( ':label', ucfirst($label), PDO::PARAM_STR );
		return $statement->execute ();
	}
	/**
	 *
	 * @since 09/2021
	 * @param AccountingEntry $ae
	 * @param string $label
	 * @return boolean
	 */
	public function untagAccountingEntry(AccountingEntry $ae, $label = NULL) {
		$sql = 'DELETE FROM tag';
		$sql .= ' WHERE accounting_entry_id=:ae_id';
		if (isset ( $label )) {
			$sql .= ' AND label=:label';
		}
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':ae_id', $ae->getId (), PDO::PARAM_INT );
		if (isset ( $label )) {
			$statement->bindValue ( ':label', $label, PDO::PARAM_STR );
		}
		return $statement->execute () ? 'requête OK' : 'requête KO';
	}
	/**
	 * @since 09/2021
	 */
	public function getTagSpendingStats($label, Account $a = Null) {
		$sql = 'SELECT SUM(ae.amount) AS amount, YEAR(ae.date) AS year, MONTH(ae.date) AS month';
		$sql .= ' FROM tag AS t INNER JOIN accounting_entry AS ae ON (t.accounting_entry_id = ae.id)';
		$criteria = array ();
		$criteria [] = 't.label=:label';
		$criteria [] = '(YEAR(ae.date) > YEAR(NOW())-2)';
		$criteria [] = 'ae.type=\'spending\'';
		if (! is_null ( $a )) {
			$criteria [] = 'ae.account_id=:account_id';
		}
		$sql .= ' WHERE ' . implode ( ' AND ', $criteria );
		$sql .= ' GROUP BY YEAR(ae.date), MONTH(ae.date)';
		$sql .= ' ORDER BY YEAR(ae.date) DESC, MONTH(ae.date) ASC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':label', $label, PDO::PARAM_STR );
		if (! is_null ( $a )) {
			$statement->bindValue ( ':account_id', $a->getId (), PDO::PARAM_INT );
		}
		$statement->execute ();
		
		$output = array();
		
		foreach ($statement->fetchAll ( PDO::FETCH_ASSOC ) as $row ) {
			
			if (!isset($output[$row['year']])) {
				$output[$row['year']] = $row['year'] == date('Y') ? array_fill(1, date('n'), null) : array_fill(1, 12, null);
			}
			
			$output[$row['year']][$row['month']] = $row['amount'];
		}
		
		//echo $statement->debugDumpParams();
		return $output;
	}
	/**
	 *
	 * @since 09/2021
	 * @version 12/2021
	 * @param Account $a
	 */
	public function getTagSpendingAccountingEntries($label, Account $a = Null) {
		$sql = 'SELECT ae.*, GROUP_CONCAT(t2.label ORDER BY t2.label ASC SEPARATOR \',\') AS tags';
		$sql .= ' FROM tag AS t INNER JOIN accounting_entry AS ae ON (t.accounting_entry_id = ae.id)';
		$sql .= ' LEFT JOIN tag AS t2 ON (t2.accounting_entry_id = ae.id AND STRCMP(t2.label, t.label) != 0)';
		$criteria = array ();
		$criteria [] = 't.label=:label';
		$criteria [] = 'ae.type=\'spending\'';
		
		if (! is_null ( $a )) {
			$criteria [] = 'ae.account_id=:account_id';
		}
		$sql .= ' WHERE ' . implode ( ' AND ', $criteria );
		$sql .= ' GROUP BY ae.id';
		$sql .= ' ORDER BY ae.date DESC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':label', $label, PDO::PARAM_STR );
		if (! is_null ( $a )) {
			$statement->bindValue ( ':account_id', $a->getId (), PDO::PARAM_INT );
		}
		$statement->execute ();
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		//$statement->debugDumpParams();
		$output = array ();
		foreach ( $rows as $r ) {
			$e = new AccountingEntry ();
			$e->setId ( $r ['id'] );
			$e->setDate ( $r ['date'] );
			$e->setValueDate ( $r ['value_date'] );
			$e->setDescription ( $r ['description'] );
			$e->setTags( $r ['tags'] );
			$e->setAmount ( $r ['amount'] );
			$e->setType ( $r ['type'] );
			$e->setTimestamp ( $r ['timestamp'] );
			$output [] = clone $e;
			unset ( $e );
		}
		return $output;
	}
	/**
	 * @since 04/2022
	 */
	public function getTags() {
		$sql = 'SELECT DISTINCT label FROM tag ORDER BY label ASC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->execute ();
		$tags = $statement->fetchAll(PDO::FETCH_COLUMN);
		return array_map('ucfirst', $tags);
	}
}
?>