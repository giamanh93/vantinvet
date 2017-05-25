<?php
// namespace components\com_jmap\views\sitemap;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Main view class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @since 3.5
 */
class JMapViewGeositemap extends JMapView {
	/**
	 * Display the XML sitemap
	 * @access public
	 * @return void
	 */
	function display($tpl = null) {
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/xml');
		
		$this->cparams = $this->getModel()->getComponentParams();

		$uriInstance = JUri::getInstance();
		if($this->cparams->get('append_livesite', true)) {
			$customHttpPort = trim($this->cparams->get('custom_http_port', ''));
			$getPort = $customHttpPort ? ':' . $customHttpPort : '';

			$getPath = null;
			if(!$this->cparams->get('sitemap_links_sef', 0)) {
				$getPath = str_replace('/index.php', '', $uriInstance->getPath());
			}
	
			$customDomain = trim($this->cparams->get('custom_sitemap_domain', ''));
			$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost() . $getPath, '/');
		
			$this->liveSite = rtrim($getDomain . $getPort, '/');
		} else {
			$this->liveSite = null;
		}
		
		// Is there a language in the url?
		$urlLanguage = null;
		$language = $this->getModel()->getState('language', null);
		if(JMapLanguageMultilang::isEnabled() && $language) {
			$urlLanguage = '&amp;lang=' . $language;
		}
		
		$this->kmlLink = 'index.php?option=com_jmap&amp;view=geositemap&amp;format=kml' . $urlLanguage;
		
		$this->setLayout('default');
		parent::display($tpl);
	}
}