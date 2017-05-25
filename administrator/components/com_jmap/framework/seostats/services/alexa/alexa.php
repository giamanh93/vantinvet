<?php
// namespace administrator\components\com_jmap\framework\seostats\services\alexa;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage alexa
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Alexa stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage alexa
 * @since 3.0
 */
class JMapSeostatsServicesAlexa extends JMapSeostats {
	/**
	 * Used for cache
	 * 
	 * @access protected 
	 * @static
	 * @var DOMXPath
	 */
	protected static $_xpath = false;
	
	/**
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $_rankKeys = array (
			'1d' => 0,
			'7d' => 0,
			'1m' => 0,
			'3m' => 0 
	);
	
	/**
	 * @access protected
	 * @static
	 * @return DOMXPath
	 */
	protected static function _getXPath($url) {
		$url = parent::getUrl ( $url );
		if (parent::getLastLoadedUrl () == $url && self::$_xpath) {
			return self::$_xpath;
		}
	
		$html = static::_getAlexaPage ( $url );
		$doc = parent::_getDOMDocument ( $html );
		$xpath = parent::_getDOMXPath ( $doc );
	
		self::$_xpath = $xpath;
	
		return $xpath;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return string
	 */
	protected static function _getAlexaPage($url) {
		$domain = JMapSeostatsHelperUrl::parseHost ( $url );
		$dataUrl = sprintf ( JMapSeostatsServices::$ALEXA_SITEINFO_URL, $domain );
		$html = static::_getPage ( $dataUrl );
		return $html;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return int
	 */
	protected static function retInt($str) {
		$strim = trim ( str_replace ( ',', '', $str ) );
		$intStr = 0 < strlen ( $strim ) ? $strim : '0';
		return intval ( $intStr );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpaths($xpathDom, $xpathQueryList) {
		foreach ( $xpathQueryList as $query ) {
			$nodes = @$xpathDom->query ( $query );
				
			if ($nodes->length != 0) {
				return $nodes;
			}
		}
	
		return null;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsGetValue($xpathDom, $xpathQueryList) {
		$nodes = static::parseDomByXpaths ( $xpathDom, $xpathQueryList );
	
		return ($nodes) ? $nodes->item ( 0 )->nodeValue : null;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return Object if results are found, false otherwise
	 */
	protected static function parseDomByXpathsGetObjectArray($xpathDom, $xpathQueryList, $associativeArrayKey) {
		$nodes = static::parseDomByXpaths ( $xpathDom, $xpathQueryList );
		
		// Iterate over DOMNodeList
		if(is_object($nodes) && $nodes->length > 0) {
			// Init the main container object
			$dataObject = new stdClass();
			$dataObject->data = array();
			
			foreach ($nodes as $node) {
				$dataObject->data[] = array($associativeArrayKey => $node->nodeValue);
			}
			
			return $dataObject;
		}
		return false;
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsToInteger($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
		return self::retInt ( $nodeValue );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsWithoutTags($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
	
		return strip_tags ( $nodeValue );
	}
	
	/**
	 * @access protected
	 * @static
	 * @return mixed nodeValue
	 */
	protected static function parseDomByXpathsToIntegerWithoutTags($xpathDom, $xpathQueryList) {
		$nodeValue = static::parseDomByXpathsGetValue ( $xpathDom, $xpathQueryList );
	
		if ($nodeValue === null) {
			return parent::noDataDefaultValue ();
		}
	
		return self::retInt ( strip_tags ( $nodeValue ) );
	}
	
	/**
	 * Get yesterday's rank
	 * 
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getDailyRank($url = false) {
		self::setRankingKeys ( $url );
		if (0 == self::$_rankKeys ['1d']) {
			return parent::noDataDefaultValue ();
		}
		
		$xpath = self::_getXPath ( $url );
		$nodes = @$xpath->query ( "//*[@id='rank']/table/tr[" . self::$_rankKeys ['1d'] . "]/td[1]" );
		
		return ! $nodes->item ( 0 ) ? parent::noDataDefaultValue () : self::retInt ( strip_tags ( $nodes->item ( 0 )->nodeValue ) );
	}
	
	/**
	 * Get the average rank over the last 7 days
	 * 
	 * @access public
	 * @static 
	 * @return int
	 */
	public static function getWeeklyRank($url = false) {
		self::setRankingKeys ( $url );
		if (0 == self::$_rankKeys ['7d']) {
			return parent::noDataDefaultValue ();
		}
		
		$xpath = self::_getXPath ( $url );
		$nodes = @$xpath->query ( "//*[@id='rank']/table/tr[" . self::$_rankKeys ['7d'] . "]/td[1]" );
		
		return ! $nodes->item ( 0 ) ? parent::noDataDefaultValue () : self::retInt ( strip_tags ( $nodes->item ( 0 )->nodeValue ) );
	}
	
	/**
	 * Get the average rank over the last month
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function getMonthlyRank($url = false) {
		self::setRankingKeys ( $url );
		if (0 == self::$_rankKeys ['1m']) {
			return parent::noDataDefaultValue ();
		}
		
		$xpath = self::_getXPath ( $url );
		$nodes = @$xpath->query ( "//*[@id='rank']/table/tr[" . self::$_rankKeys ['1m'] . "]/td[1]" );
		
		return ! $nodes->item ( 0 ) ? parent::noDataDefaultValue () : self::retInt ( strip_tags ( $nodes->item ( 0 )->nodeValue ) );
	}
	
	/**
	 * Get the average rank over the last 3 months
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function getGlobalRank($url = false) {
		/*
		 * self::setRankingKeys($url); if (0 == self::$_rankKeys['3m']) { return parent::noDataDefaultValue(); }
		 */
		$xpath = self::_getXPath ( $url );
		
		$xpathQueryList = array (
				"//*[@id='traffic-rank-content']/div/span[2]/div[1]/span/span/div/strong",
				"//*[@id='traffic-rank-content']/div/span[2]/div[1]/span/span/div/strong/a" 
		);
		
		return static::parseDomByXpathsToIntegerWithoutTags ( $xpath, $xpathQueryList );
	}
	
	/**
	 * Get the average rank over the week
	 * 
	 * @access public
	 * @static  
	 * @return int
	 */
	public static function setRankingKeys($url = false) {
		$xpath = self::_getXPath ( $url );
		$nodes = @$xpath->query ( "//*[@id='rank']/table/tr" );
		
		if (5 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 2,
					'7d' => 3,
					'1m' => 4,
					'3m' => 5 
			);
		} else if (4 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 2,
					'1m' => 3,
					'3m' => 4 
			);
		} else if (3 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 0,
					'1m' => 2,
					'3m' => 3 
			);
		} else if (2 == $nodes->length) {
			self::$_rankKeys = array (
					'1d' => 0,
					'7d' => 0,
					'1m' => 0,
					'3m' => 2 
			);
		}
	}
	
	/**
	 * Get the rank by country
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getCountryRank($url = false) {
		$xpath = self::_getXPath ( $url );
		$node1 = self::parseDomByXpaths ( $xpath, array (
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/h4/a",
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/h4/strong/a" 
		) );
		
		$node2 = self::parseDomByXpaths ( $xpath, array (
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/div/strong/a",
				"//*[@id='traffic-rank-content']/div/span[2]/div[2]/span/span/div/strong" 
		) );
		
		if (! is_null ( $node2 ) && $node2->item ( 0 )) {
			$rank = self::retInt ( strip_tags ( $node2->item ( 0 )->nodeValue ) );
			if ($node1->item ( 0 ) && 0 != $rank) {
				return array (
						'rank' => $rank,
						'country' => $node1->item ( 0 )->nodeValue 
				);
			}
		}
		
		return parent::noDataDefaultValue ();
	}
	
	/**
	 * Get backlinks count
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBacklinkCount($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$queryList = array (
				"//section[@class='row-fluid panel-wrapper '][6]/section/div/span/div/span",
				"//*[@id='linksin_div']/section/div/div[1]/span",
				"//*[@id='linksin-panel']/section/div/span/div/span"
		);
		
		return static::parseDomByXpathsToInteger ( $xpath, $queryList );
	}
	
	/**
	 * Get bounce rate
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getBounceRate($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$queryList = array (
				"//*[@id='engagement-content']/span/span/span/div/strong"
		);
	
		return static::parseDomByXpathsWithoutTags ( $xpath, $queryList );
	}
	
	/**
	 * Get keywords list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getKeywords($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$queryList = array (
				"//table[@id='keywords_top_keywords_table']/tbody/tr/td[1]/span[position()=last()]"
		);
	
		return static::parseDomByXpathsGetObjectArray ( $xpath, $queryList, 'Ph' );
	}
	
	/**
	 * Get competitors list
	 *
	 * @access public
	 * @static
	 * @return Object
	 */
	public static function getCompetitors($url = false) {
		$xpath = self::_getXPath ( $url );
	
		$queryList = array (
				"//table[@id='related_link_table']/tbody/tr/td/a"
		);

		return static::parseDomByXpathsGetObjectArray ( $xpath, $queryList, 'Dn' );
	}
	
	/**
	 * Get page load time
	 *
	 * @access public
	 * @static
	 * @return int
	 */
	public static function getPageLoadTime($url = false) {
		$xpath = self::_getXPath ( $url );
		
		$queryList = array (
				"//section[@class='row-fluid panel-wrapper '][9]/section/p",
				"//*[@id='section-load']/div/section/p",
				"//*[@id='loadspeed-panel']/section/p"
		);
		
		return static::parseDomByXpathsWithoutTags ( $xpath, $queryList );
	}
	
	/**
	 *
	 * @access public
	 * @static
	 * @param integer $type
	 *        	Specifies the graph type. Valid values are 1 to 6.
	 * @param integer $width
	 *        	Specifies the graph width (in px).
	 * @param integer $height
	 *        	Specifies the graph height (in px).
	 * @param integer $period
	 *        	Specifies the displayed time period. Valid values are 1 to 12.
	 * @return string Returns a string, containing the HTML code of an image, showing Alexa Statistics as Graph.
	 */
	public static function getTrafficGraph($type = 1, $url = false, $w = 660, $h = 330, $period = 1, $html = true) {
		$url = self::getUrl ( $url );
		$domain = JMapSeostatsHelperUrl::parseHost ( $url );
		
		switch ($type) {
			case 1 :
				$gtype = 't';
				break;
			case 2 :
				$gtype = 'p';
				break;
			case 3 :
				$gtype = 'u';
				break;
			case 4 :
				$gtype = 's';
				break;
			case 5 :
				$gtype = 'b';
				break;
			case 6 :
				$gtype = 'q';
				break;
			default :
				break;
		}
		
		$imgUrl = sprintf ( JMapSeostatsServices::$ALEXA_GRAPH_URL, $gtype, $w, $h, $period, $domain );
		$imgTag = '<img src="%s" width="%s" height="%s" alt="Alexa Statistics Graph for %s"/>';
		
		return ! $html ? $imgUrl : sprintf ( $imgTag, $imgUrl, $w, $h, $domain );
	}
}
