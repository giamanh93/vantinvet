<?php
// namespace components\com_jmap\models;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main sitemap model public responsibilities interface
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @since 3.5
 */
interface IJMapModelSitemap {
	/**
	 * Get the Data
	 * @access public
	 * @param Object $httpClient
	 * @return Object
	 */
	public function getSitemapData(JMapHttp $httpClient);
}

/**
 * Main sitemap model class <<testable_behavior>>
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @since 3.5
 */
class JMapModelGeositemap extends JMapModel implements IJMapModelSitemap {
	/**
	 * Get the Data
	 * @access public
	 * @param Object $httpClient
	 * @return Object
	 */
	public function getSitemapData(JMapHttp $httpClient) {
		// Try to get informations
		try {
			$address = $this->getComponentParams()->get('geositemap_address', null);
			if(!$address) {
				throw new Exception(null);
			}
			$encodedAddress = urlencode($address);
			
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $encodedAddress;
			$response = $httpClient->get($url)->body;
			if($response) {
				$decodedUpdateInfos = json_decode($response);
			}
			
			if(!is_object($decodedUpdateInfos) || !is_array($decodedUpdateInfos->results) || $decodedUpdateInfos->status != 'OK') {
				throw new Exception(null);
			}
			
			return $decodedUpdateInfos->results[0];
		} catch(JMapException $e) {
			return false;
		}  catch(Exception $e) {
			return false;
		}

	}
}