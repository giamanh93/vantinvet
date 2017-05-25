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
		$document->setMimeEncoding('application/vnd.google-earth.kml+xml');
		
		// Get geolocation data, go on only if they are valid
		$this->data = $this->getModel()->getSitemapData($this->get('httpclient'));
		if(!$this->data) {
			return false;
		}
		
		$this->cparams = $this->getModel()->getComponentParams();
		$this->joomlaConfig = JFactory::getConfig();

		$this->setLayout('default');
		parent::display($tpl);
	}
}