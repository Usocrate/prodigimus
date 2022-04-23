<?php
/**
 * @package usocrate.prodigimus
 * @author Florent
 * @since 12/2010
 */
class ToolBox {
	public static function hex2rgba(string $input, float $opacity = 1) {
		preg_match ( "/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i", $input, $out );

		//var_dump ( $out );

		return 'rgba(' . hexdec ( $out [1] ) . ',' . hexdec ( $out [2] ) . ',' . hexdec ( $out [3] ) . ',' . $opacity . ')';
	}
	/**
	 *
	 * @version 02/2017
	 */
	public static function getHtmlPagesNav($page_index = 1, $pages_nb, $param, $page_index_param_name = 'page_index') {
		// construction de l'url de base des liens
		$url_param = is_array ( $param ) ? self::arrayToUrlParam ( $param ) : $param;
		if (iconv_strlen ( $url_param ) > 0) {
			$url_param .= '&';
		}
		$url_base = $_SERVER ['PHP_SELF'] . '?' . $url_param;

		$empan = 3;

		$output = '<ul class="pagination">';

		// première page
		if ($page_index > 2) {
			$output .= '<li><a href="' . $url_base . $page_index_param_name . '=1">&lt;&lt;</a></li>';
		} else {
			$output .= '<li class="disabled"><span>&lt;&lt;</span></li>';
		}

		// page précédente
		if ($page_index > 1) {
			$output .= '<li><a href="' . $url_base . $page_index_param_name . '=' . ($page_index - 1) . '">&lt;</a></li>';
		} else {
			$output .= '<li class="disabled"><span>&lt;</span></li>';
		}

		// autres pages
		for($i = ($page_index - $empan); $i <= ($page_index + $empan); $i ++) {
			if ($i < 1 || $i > $pages_nb) {
				continue;
			}
			if ($i == $page_index) {
				$output .= '<li class="active"><span>' . $i . '</span></li>';
			} else {
				$output .= '<li><a href="' . $url_base . $page_index_param_name . '=' . $i . '">' . $i . '</a></li>';
			}
		}

		// page suivante
		if ($page_index < $pages_nb) {
			$output .= '<li><a href="' . $url_base . $page_index_param_name . '=' . ($page_index + 1) . '">&gt;</a></li>';
		} else {
			$output .= '<li class="disabled"><span>&gt;</span></li>';
		}

		// dernière page
		if ($page_index < ($pages_nb - 1)) {
			$output .= '<li><a href="' . $url_base . $page_index_param_name . '=' . $pages_nb . '">&gt;&gt;</a></li>';
		} else {
			$output .= '<li class="disabled"><span>&gt;&gt;</span></li>';
		}
		$output .= '</ul>';
		return $output;
	}
	/**
	 * Transforme un tableau en chaîne de paramètres à intégrer dans une url.
	 *
	 * @param
	 *        	$array
	 * @return string
	 * @version 04/2009
	 */
	public static function arrayToUrlParam($array) {
		if (is_array ( $array )) {
			$params = array ();
			foreach ( $array as $clé => $valeur ) {
				if (isset ( $valeur ))
					$params [] = $clé . '=' . urlencode ( $valeur );
			}
			return implode ( '&', $params );
		}
		return false;
	}
	/**
	 * Convertit tous les caractères de balisage d'une chaîne en entités Xml ("&amp;", "&lt;", "&gt;", "&apos;" et "&quot;")
	 *
	 * @since 09/2006
	 */
	public static function xmlEntities($input) {
		$search = array (
				'&',
				'<',
				'>',
				'\'',
				'"'
		);
		$replace = array (
				'&amp;',
				'&lt;',
				'&gt;',
				'&apos;',
				'&quot;'
		);
		return str_replace ( $search, $replace, $input );
	}
	/**
	 *
	 * @version 02/2022
	 */
	public static function toHtml($input) {
		return is_string ( $input ) ? htmlentities ( $input, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '';
	}
	public static function sans_accent($chaine) {
		$accent = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
		$noaccent = "aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby";
		return strtr ( trim ( $chaine ), $accent, $noaccent );
	}
	/**
	 * Elimine les caractères indésirables pour qu'une chaîne de caractère devienne utilisable comme nom de fichier.
	 *
	 * @return string
	 * @since 16/09/2005
	 */
	public static function formatForFileName($input) {
		$input = self::sans_accent ( $input );
		$input = strtolower ( $input );
		$input = str_replace ( ' ', '-', $input );
		return $input;
	}
	/**
	 * Formatte les données postées via formulaire pour les enregistrer en base.
	 *
	 * @version 01/2011
	 */
	public static function formatUserPost($data) {
		if (is_array ( $data )) {
			array_walk ( $data, 'ToolBox::formatUserPost' );
		} else {
			$data = strip_tags ( $data );
			$data = html_entity_decode ( $data, ENT_QUOTES, 'UTF-8' );
			$data = trim ( $data );
		}
	}
	/**
	 * Ajoute un répertoire dans la liste des répertoires utilisés dans la recherche de fichiers à inclure.
	 *
	 * @since 02/2007
	 */
	public static function addIncludePath($input) {
		return ini_set ( 'include_path', $input . PATH_SEPARATOR . ini_get ( 'include_path' ) );
	}
}