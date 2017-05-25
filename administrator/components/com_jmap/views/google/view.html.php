<?php
// namespace administrator\components\com_jmap\views\overview;
/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage views
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage views
 * @subpackage google
 * @since 3.1
 */
class JMapViewGoogle extends JMapView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$user = JFactory::getUser();
		if($this->getModel()->getState('googlestats', 'analytics') == 'webmasters') {
			JToolBarHelper::title( JText::_( 'COM_JMAP_GOOGLE_WEBMASTERS_TOOLS' ), 'jmap' );
		} else {
			JToolBarHelper::title( JText::_( 'COM_JMAP_GOOGLE_ANALYTICS' ), 'jmap' );
		}

		// Store logged in status in session
		if($this->isLoggedIn) {
			JToolBarHelper::custom('google.deleteEntity', 'lock', 'lock', 'COM_JMAP_GOOGLE_LOGOUT', false);
		}
		
		if ($user->authorise('core.edit', 'com_jmap') && $this->getModel()->getState('googlestats', 'analytics') == 'webmasters' && $this->isLoggedIn) {
			JToolBarHelper::custom('google.submitSitemap', 'upload', 'upload', 'COM_JMAP_SUBMIT_SITEMAP', false);
		}
		
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Default display listEntities
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		// Get main records
		$lists = $this->get ( 'Lists' );
		
		$this->loadJQuery($this->document);
		$this->loadBootstrap($this->document);
		$this->document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/google.js' );
		
		// Check the Google stats type and retrieve stats data accordingly, supported types are 'analytics' and 'webmasters'
		if($this->getModel()->getState('googlestats', 'analytics') == 'webmasters') {
			$googleData = $this->get ( 'DataWebmasters' );
			if(!$this->getModel()->getState('loggedout')) {
				$tpl = 'webmasters';
			}
			// Load resources
			$this->loadJQueryUI($this->document); // Required for calendar feature
			$this->document->addScriptDeclaration("jQuery(function(){jQuery('input[data-role=calendar]').datepicker({dateFormat : 'yy-mm-dd'}).prev('span').on('click', function(){jQuery(this).datepicker('show');});});");
			$this->document->addScript(JURI::root(true) . '/administrator/components/com_jmap/js/tablesorter/jquery.tablesorter.js');

			// Set dates
			$dates = array('from'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
			$this->dates = $dates;
		} else {
			$googleData = $this->get ( 'Data' );
		}
		
		// Inject js translations
		$translations = array(
				'COM_JMAP_REQUIRED',
				'COM_JMAP_ADDSITEMAP',
				'COM_JMAP_ADDSITEMAP_DESC',
				'COM_JMAP_SUBMIT',
				'COM_JMAP_CANCEL',
				'COM_JMAP_INVALID_URL_FORMAT',
				'COM_JMAP_WORKING'
		);
		$this->injectJsTranslations($translations, $this->document);
		
		$this->globalConfig = JFactory::getConfig();
		$this->timeZoneObject = new DateTimeZone($this->globalConfig->get('offset'));
		$this->document->addScriptDeclaration("var jmap_baseURI='" . JUri::root() . "';");
		$this->lists = $lists;
		$this->googleData = $googleData;
		$this->isLoggedIn = $this->getModel()->getToken();
		$this->statsDomain = $this->getModel()->getState('stats_domain', JUri::root());
		$this->errorsDomain = preg_match('/^http/i', $this->statsDomain) ? $this->statsDomain . '/' : JUri::getInstance()->getScheme() . '://' . $this->statsDomain . '/';
		$this->hasOwnCredentials = $this->getModel()->getState('has_own_credentials', false);
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ($tpl);
	}
}