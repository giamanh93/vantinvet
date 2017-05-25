<?php
// namespace administrator\components\com_jmap\framework\seostats\services\semrush;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage semrush
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * SEMRush stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage semrush
 * @since 4.0
 */
class JMapSeostatsServicesSemrush extends JMapSeostats {
	public static function getDBs() {
		return array (
				"au", // Google.com.au (Australia)
				"br", // Google.com.br (Brazil)
				"ca", // Google.ca (Canada)
				"de", // Google.de (Germany)
				"es", // Google.es (Spain)
				"fr", // Google.fr (France)
				"it", // Google.it (Italy)
				"ru", // Google.ru (Russia)
				"uk", // Google.co.uk (United Kingdom)
				'us', // Google.com (United States)
				"us.bing"  # Bing.com
        );
	}
	
	/**
	 * Returns the SEMRush main report data.
	 * (Only main report is public available.)
	 *
	 * @access public
	 * @param
	 *        	url string Domain name only, eg. "ebay.com" (/wo quotes).
	 * @param
	 *        	db string Optional: The database to use. Valid values are:
	 *        	au, br, ca, de, es, fr, it, ru, uk, us, us.bing (us is default)
	 * @return array Returns an array containing the main report data.
	 * @link http://www.semrush.com/api.html
	 */
	public static function getDomainRank($url = false, $db = false) {
		$data = self::getBackendData ( $url, $db, 'domain_rank' );
		if(is_array($data) && isset($data ['rank'] ['data'] [0] ['Rk']) && is_numeric($data ['rank'] ['data'] [0] ['Rk'])) {
			return $data ['rank'] ['data'] [0] ['Rk'];
		} else {
			return parent::noDataDefaultValue ();
		}
	}
	public static function getDomainRankHistory($url = false, $db = false) {
		$data = self::getBackendData ( $url, $db, 'domain_rank_history' );
		
		return is_array ( $data ) ? $data ['rank_history'] : $data;
	}
	public static function getOrganicKeywords($url = false, $db = false) {
		return static::getWidgetData ( $url, $db, 'organic', 'organic' );
	}
	public static function getCompetitors($url = false, $db = false) {
		return static::getWidgetData ( $url, $db, 'organic_organic', 'organic_organic' );
	}
	public static function getDomainGraph($reportType = 1, $url = false, $db = false, $w = 400, $h = 300, $lc = 'e43011', $dc = 'e43011', $lang = 'en', $html = true) {
		$domain = static::getDomainFromUrl ( $url );
		$database = static::getValidDatabase ( $db );
		
		$imgUrl = sprintf ( JMapSeostatsServices::SEMRUSH_GRAPH_URL, $domain, $database, $reportType, $w, $h, $lc, $dc, $lang );
		
		if (! $html) {
			return $imgUrl;
		} else {
			$imgTag = '<img src="%s" id="semrush_chart" alt="SEMRush Domain Trend Graph for %s"/>';
			return sprintf ( $imgTag, $imgUrl, $domain );
		}
	}
	protected static function getApiData($url) {
		$json = static::_getPage ( $url );
		return json_decode ( $json, true );
	}
	protected static function getSemRushDatabase($db) {
		return false !== $db ? $db : JMapSeostatsServices::SEMRUSH_DB;
	}
	protected static function guardDomainIsValid($domain) {
		if (false == $domain) {
			self::exc ( 'Invalid domain name.' );
		}
	}
	protected static function guardDatabaseIsValid($database) {
		if (false === $database) {
			self::exc ( 'db' );
		}
	}
	protected static function getBackendData($url, $db, $reportType) {
		$db = false !== $db ? $db : JMapSeostatsServices::SEMRUSH_DB;
		$dataUrl = self::getBackendUrl ( $url, $db, $reportType );
		$data = self::getApiData ( $dataUrl );
		
		if (! is_array ( $data )) {
			$data = self::getApiData ( str_replace ( '.backend.', '.api.', $dataUrl ) );
			if (! is_array ( $data )) {
				return parent::noDataDefaultValue ();
			}
		}
		
		return $data;
	}
	protected static function getBackendUrl($url, $db, $reportType) {
		$domain = static::getDomainFromUrl ( $url );
		$database = static::getValidDatabase ( $db );
		
		$backendUrl = JMapSeostatsServices::SEMRUSH_BE_URL;
		return sprintf ( $backendUrl, $database, $reportType, $domain );
	}
	protected static function getWidgetUrl($url, $db, $reportType) {
		$domain = static::getDomainFromUrl ( $url );
		$database = static::getValidDatabase ( $db );
		
		$widgetUrl = JMapSeostatsServices::SEMRUSH_WIDGET_URL;
		return sprintf ( $widgetUrl, $reportType, $database, $domain );
	}
	protected static function getWidgetData($url, $db, $reportType, $valueKey) {
		$db = false !== $db ? $db : JMapSeostatsServices::SEMRUSH_DB;
		$dataUrl = self::getWidgetUrl ( $url, $db, $reportType );
		$data = self::getApiData ( $dataUrl );
		
		return ! is_array ( $data ) ? parent::noDataDefaultValue () : $data [$valueKey];
	}
	protected static function checkDatabase($db) {
		return ! in_array ( $db, self::getDBs () ) ? false : $db;
	}
	
	/**
	 *
	 * @throws Exception
	 */
	protected static function exc($err) {
		$e = ($err == 'db') ? "Invalid database. Choose one of: " . substr ( implode ( ", ", self::getDBs () ), 0, - 2 ) : $err;
		throw new Exception ( $e );
		exit ( 0 );
	}
	protected static function getDomainFromUrl($url) {
		$url = parent::getUrl ( $url );
		$domain = JMapSeostatsHelperUrl::parseHost ( $url );
		static::guardDomainIsValid ( $domain );
		
		return $domain;
	}
	protected static function getValidDatabase($db) {
		$db = self::getSemRushDatabase ( $db );
		$database = self::checkDatabase ( $db );
		static::guardDatabaseIsValid ( $database );
		
		return $database;
	}
}
