<?php
// namespace administrator\components\com_jmap\framework\seostats;
/**
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Base class for SEO stats services
 *
 * @package JMAP::SEOSTATS::administrator::components::com_jmap
 * @subpackage seostats
 * @since 3.0
 */
class JMapSeostats {
	/**
	 *
	 * @access protected
	 * @var string
	 */
	protected static $_url;
	
	/**
	 *
	 * @access protected
	 * @var string
	 */
	protected static $_host;
	
	/**
	 *
	 * @access protected
	 * @var string
	 */
	protected static $_lastHtml;
	
	/**
	 *
	 * @access protected
	 * @var string
	 */
	protected static $_lastLoadedUrl = false;
	
	/**
	 *
	 * @access protected
	 * @return DOMDocument
	 */
	protected static function _getDOMDocument($html) {
		$doc = new DOMDocument ();
		@$doc->loadHtml ( $html );
		return $doc;
	}
	
	/**
	 *
	 * @access protected
	 * @return DOMXPath
	 */
	protected static function _getDOMXPath($doc) {
		$xpath = new DOMXPath ( $doc );
		return $xpath;
	}
	
	/**
	 *
	 * @access protected
	 * @return HTML string
	 */
	protected static function _getPage($url) {
		$url = self::getUrl ( $url );
		if (self::getLastLoadedUrl () == $url) {
			return self::getLastLoadedHtml ();
		}
		
		$html = JMapSeostatsHelperHttpcurl::sendRequest ( $url );
		if ($html) {
			self::$_lastLoadedUrl = $url;
			self::_setHtml ( $html );
			return $html;
		} else {
			self::noDataDefaultValue ();
		}
	}
	
	/**
	 *
	 * @access protected
	 * @return void
	 */
	protected static function _setHtml($str) {
		self::$_lastHtml = $str;
	}
	
	/**
	 *
	 * @access protected
	 * @return string
	 */
	protected static function noDataDefaultValue() {
		return JText::_ ( 'COM_JMAP_NA' );
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public static function getLastLoadedHtml() {
		return self::$_lastHtml;
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public static function getLastLoadedUrl() {
		return self::$_lastLoadedUrl;
	}
	
	/**
	 * Ensure the URL is set, return default otherwise
	 *
	 * @static
	 *
	 *
	 *
	 *
	 * @access public
	 * @return string
	 */
	public static function getUrl($url = false) {
		$url = false !== $url ? $url : self::$_url;
		return $url;
	}
	
	/**
	 *
	 * @access public
	 * @return boolean
	 */
	public function setUrl($url) {
		if (false !== JMapSeostatsHelperUrl::isRfc ( $url )) {
			self::$_url = $url;
			self::$_host = JMapSeostatsHelperUrl::parseHost ( $url );
		} else {
			throw new JMapException ( JText::_('COM_JMAP_INVALID_URL'), 'error' );
		}
		return true;
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public static function getHost($url = false) {
		return JMapSeostatsHelperUrl::parseHost ( self::getUrl ( $url ) );
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public static function getDomain($url = false) {
		return 'http://' . self::getHost ( $url = false );
	}
	
	/**
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($url = false) {
		if (false !== $url) {
			self::setUrl ( $url );
		}
	}
}