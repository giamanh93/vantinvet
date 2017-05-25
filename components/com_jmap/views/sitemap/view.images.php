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
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main view class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @since 1.0
 */
class JMapViewSitemap extends JMapView {
	/**
	 * Display the XML sitemap
	 * @access public
	 * @return void
	 */
	function display($tpl = null) {
		$document = $this->document;
		$document->setMimeEncoding('application/xml');
		
		// Call by cache handler get no params, so recover from model state
		if(!$tpl) {
			$tpl = $this->getModel ()->getState ( 'documentformat' );
		}
				   
		$this->data = $this->get ( 'SitemapData' );
		$this->cparams = $this->getModel ()->getState ( 'cparams' );
		// Transport wrapper
		$this->HTTPClient = new JMapHttp(null, $this->cparams);
		$this->application = JFactory::getApplication();
		$this->xslt = $this->getModel()->getState('xslt');
		
		// Set regex for the images crawler
		$this->mainImagesRegex = $this->cparams->get('regex_images_crawler', 'advanced') == 'standard' ?
										  '/(<img)([^>])*(src=["\']([^"\']+)["\'])([^>])*/i' : '/(<img)([^>])*(src=["\']?([^"\']+\.(jpg|jpeg|gif|png))["\']?)([^>])*/i';
		
		$uriInstance = JURI::getInstance();
		$customHttpPort = trim($this->cparams->get('custom_http_port', ''));
		$getPort = $customHttpPort ? ':' . $customHttpPort : '';
		
		$customDomain = trim($this->cparams->get('custom_sitemap_domain', ''));
		$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');

		if($this->cparams->get('append_livesite', true)) {
			$this->liveSite = rtrim($getDomain . $getPort, '/');
		} else {
			$this->liveSite = null;
		}
		
		// Initialize output links buffer with exclusion for links
		$this->outputtedLinksBuffer = $this->getModel()->getExcludedLinks($this->liveSite);
		
		// Crawler live site management
		if($this->cparams->get('sh404sef_multilanguage', 0) && JMapLanguageMultilang::isEnabled()) {
			$lang = '/' . $this->app->input->get('lang');
			// Check if sh404sef insert language code param is off, otherwise the result would be doubled language chunk in liveSiteCrawler
			$sh404SefParams = JComponentHelper::getParams('com_sh404sef');
			if($sh404SefParams->get('shInsertLanguageCode', 0) || !$sh404SefParams->get('Enabled', 1)) {
				$lang = null;
			}
			$this->liveSiteCrawler = rtrim($getDomain . $getPort . $lang, '/');
		} else {
			$this->liveSiteCrawler = rtrim($getDomain . $getPort, '/');
		}
		
		$this->setLayout('default');
		parent::display ($tpl);
	}
}