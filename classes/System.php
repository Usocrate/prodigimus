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
	private $appli_name;
	private $appli_description;
	private $appli_url;
	private $dir_path;
	private $pdo;
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
	 * @since 10/2016
	 * @return boolean
	 */
	public function configFileExists() {
		return file_exists ( $this->config_file_path );
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
					'googlemaps_api_key' => $this->googlemaps_api_key,
					'dir_path' => $this->dir_path
			);
			return file_put_contents ( $this->config_file_path, json_encode ( $a ) );
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
	 * @return PDO|boolean
	 */
	public function createDatabase() {
		try {
			$pdo = new PDO ( 'mysql:host=' . $this->db_host, $this->db_user, $this->db_password );
			$pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$pdo->exec ( 'CREATE DATABASE IF NOT EXISTS `' . $this->db_name . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' );
			$pdo->exec ( 'USE  `' . $this->db_name . '` ' );
			$pdo->exec ( "
					CREATE TABLE IF NOT EXISTS `amount`(
						`id` SMALLINT(5) unsigned NOT NULL AUTO_INCREMENT,
						`title` TINYTEXT NOT NULL,
						`description` TINYTEXT,
						`value` DECIMAL(17,2),
						`currency` CHAR(3) NOT NULL DEFAULT 'EUR',
						`type` ENUM('spending', 'earning', 'giving', 'fighting', 'losing', 'illin''') NOT NULL DEFAULT 'spending',
						`source` TINYTEXT,
						`source_locator` TINYTEXT,
						`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			$pdo->exec ( "
					CREATE TABLE IF NOT EXISTS `user`(
						`id` TINYINT(5) unsigned NOT NULL AUTO_INCREMENT,
						`name` TINYTEXT NOT NULL,
						`password` TINYTEXT NOT NULL,
						`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;" );
			return true;
		} catch ( PDOException $e ) {
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
		$output [] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->getSkinUrl () . 'images/favicon-32x32.png">';
		$output [] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $this->getSkinUrl () . 'images/favicon-16x16.png">';
		$output [] = '<link rel="manifest" href="' . $this->getSkinUrl () . 'manifest.json">';
		$output [] = '<meta name="application-name" content="' . ToolBox::toHtml ( $this->getAppliName () ) . '">';
		$output [] = '<meta name="theme-color" content="#da8055">';
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
	 * @since 12/2020
	 * @param Object $o
	 * @return boolean
	 */
	public function put(Object $o) {
		switch (get_class ( $o )) {
			case 'Amount' :

				$new = empty ( $o->id );

				$settings = array ();
				if (isset ( $o->title )) {
					$settings [] = 'title=:title';
				}
				if (isset ( $o->value )) {
					$settings [] = 'value=:value';
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
				if (isset ( $o->value )) {
					$statement->bindValue ( ':value', $o->value, PDO::PARAM_STR );
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
			$output->id = $data ['id'];
			$output->title = $data ['title'];
			$output->value = $data ['value'];
			$output->source = $data ['source'];
			$output->source_url = $data ['source_url'];
			return $output;
		}
		return null;
	}
	/**
	 * @since 12/2020
	 * @return Amount[]
	 */
	public function getAmounts() {
		$sql = 'SELECT * FROM amount ORDER BY value DESC';
		$statement = $this->getPdo ()->prepare ( $sql );
		$statement->execute ();
		$rows = $statement->fetchAll ( PDO::FETCH_ASSOC );
		$output = array ();
		foreach ( $rows as $r ) 
		{
			$a = new Amount ();
			$a->id = $r ['id'];
			$a->title = $r ['title'];
			$a->value = $r ['value'];
			$a->currency = $r ['currency'];
			$a->type = $r ['type'];
			$a->source = $r ['source'];
			$a->source_url = $r ['source_url'];
			$output[] = clone $a;
			unset($a);
		}
		return $output;
	}
}
?>