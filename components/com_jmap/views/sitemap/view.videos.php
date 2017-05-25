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
		$session = JFactory::getSession();
		
		// Call by cache handler get no params, so recover from model state
		if(!$tpl) {
			$tpl = $this->getModel ()->getState ( 'documentformat' );
		}
				   
		$this->data = $this->get ( 'SitemapData' );
		$this->cparams = $this->getModel ()->getState ( 'cparams' );
		// Transport wrapper
		$this->HTTPClient = new JMapHttp(null, $this->cparams);
		// Reload $this->outputtedVideosBuffer from previous session if process_status === run, AKA an ongoing JS AJAX precaching is running
		$this->outputtedVideosBuffer = $this->app->input->get('process_status', null) === 'run' ? $session->get('com_jmap.videos_buffer') : array();
		$this->application = JFactory::getApplication();
		$this->xslt = $this->getModel()->getState('xslt');
		$apiKeys = array('AIzaSyAzwbNgPzfILcKqviEZ5Jm1KSLPcIEoIk8', 'AIzaSyAwTxEMZNDQcqoOPkCbslZ0rnyBYpSAYFY', 'AIzaSyC9sY-w9DUnqBidmtHN_cUeX5RrPe41wKY');
		$youtubeVideosApikey = $apiKeys[array_rand($apiKeys)];
		$this->videoApisEndpoints = array('youtube'=>'https://www.googleapis.com/youtube/v3/videos?id=%s&key=' . $youtubeVideosApikey . '&part=snippet,contentDetails',
										  'vimeo'=>'http://vimeo.com/api/v2/video/%s.json',
										  'dailymotion'=>'https://api.dailymotion.com/video/%s?fields=title,duration,description,thumbnail_360_url');
		$this->htmlResponseReference = null;
		
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
		
		// Add include path
		$this->addTemplatePath(JPATH_COMPONENT . '/views/sitemap/tmpl/videos');
		$this->setLayout('default');
		parent::display ($tpl);
		
		// Assign $this->outputtedVideosBuffer for next session if process_status == start/run
		if(in_array($this->app->input->get('process_status', null), array('start', 'run'))) {
			$session->set('com_jmap.videos_buffer', $this->outputtedVideosBuffer);
		}
		// Delete $this->outputtedVideosBuffer session if process_status == end
		if($this->app->input->get('process_status', null) === 'end') {
			$session->clear('com_jmap.videos_buffer');
		}
	}
}