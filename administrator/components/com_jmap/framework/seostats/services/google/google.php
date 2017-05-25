<?php
// namespace administrator\components\com_jmap\framework\seostats\services\google;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Google stats service
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage services
 * @subpackage google
 * @since 3.0
 */
class JMapSeostatsServicesGoogle extends JMapSeostats {
	/**
	 * Returns the total amount of results for a Google 'site:'-search for the object URL.
	 *
	 * @param string $url
	 *        	String, containing the query URL.
	 * @return integer Returns the total site-search result count.
	 */
	public static function getSiteindexTotal($url = false) {
		$numericValue = JText::_ ( 'COM_JMAP_NA' );
		
		$url = parent::getUrl ( $url );
		$siteQuery = JComponentHelper::getParams('com_jmap')->get('seostats_site_query', 1) ? 'site:' : null;
		$query = urlencode ( $siteQuery . $url );
		
		$totalLinksHtml = JMapSeostatsServicesGoogleSearch::getSerpsIndexedLinks ( $query );
		if($totalLinksHtml && isset($totalLinksHtml[1])) {
			$explodedChunks = explode(' ', $totalLinksHtml[1]);
			$numericValue = is_numeric(str_replace(array(',','.'), '', $explodedChunks[1])) ? str_replace(',', '.', $explodedChunks[1]) : $explodedChunks[0];
		}
		
		return $numericValue;
	}
	
	/**
	 * Public interface to get containing detailed results parsed and formatted for any Google search SERP
	 *
	 * @access public
	 * @param string $query The containing the search query.
	 * @param int $pageNumber The SERP page number requested
	 * @return array $customHeaders The custom headers for country and language to get SERP for
	 */
	public static function getSerps($query, $pageNumber = 0, $customHeaders = array()) {
		return JMapSeostatsServicesGoogleSearch::getSerps ( $query, $pageNumber, $customHeaders );
	}
	
	/**
	 * Public interface to get the ranked page for a given keyword and website domain for any Google search SERP
	 *
	 * @access public
	 * @param string $query The containing the search query.
	 * @param int $pageNumber The SERP page number requested
	 * @return array $customHeaders The custom headers for country and language to get SERP for
	 */
	public static function getRankedPageKeyword($query, $domain, $pageNumber = 0, $numResults = 100, $customHeaders = array()) {
		return JMapSeostatsServicesGoogleSearch::getRankedPageKeyword ( $query, $domain, $pageNumber, $numResults, $customHeaders );
	}
}