<?php
// namespace administrator\components\com_jmap\framework\seostats\helper;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * URL-String Helper Class
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @subpackage helper
 * @since 3.0
 */
class JMapSeostatsHelperUrl {
	/**
	 * Extract the URI host part
	 *
	 * @access public
	 * @static
	 * @param string $url
	 *        	String, containing the initialized object URL.
	 * @return string The found host
	 */
	public static function parseHost($url) {
		$url = @parse_url ( 'http://' . preg_replace ( '#^https?://#', '', $url ) );
		return (isset ( $url ['host'] ) && ! empty ( $url ['host'] )) ? $url ['host'] : false;
	}
	
	/**
	 * Validates the initialized object URL syntax.
	 *
	 * @access public
	 * @static
	 * @param string $url
	 *        	String, containing the initialized object URL.
	 * @return string Returns string, containing the validation result.
	 */
	public static function isRfc($url) {
		if (isset ( $url ) && 1 < strlen ( $url )) {
			$host = self::parseHost ( $url );
			$scheme = strtolower ( parse_url ( $url, PHP_URL_SCHEME ) );
			if (false !== $host && ($scheme == 'http' || $scheme == 'https')) {
				$pattern = '([A-Za-z][A-Za-z0-9+.-]{1,120}:[A-Za-z0-9/](([A-Za-z0-9$_.+!*,;/?:@&~=-])';
				$pattern .= '|%[A-Fa-f0-9]{2}){1,333}(#([a-zA-Z0-9][a-zA-Z0-9$_.+!*,;/?:@&~=%-]{0,1000}))?)';
				return ( bool ) preg_match ( $pattern, $url );
			}
		}
		return false;
	}
}