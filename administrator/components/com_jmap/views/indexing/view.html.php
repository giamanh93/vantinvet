<?php
// namespace administrator\components\com_jmap\views\datasets;
/**
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @since 3.3
 */
class JMapViewIndexing extends JMapView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-indexing.png")}');
	
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_('COM_JMAP_INDEXING' ), 'jmap' );
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
		$rows = $this->get('Data');
		$lists = $this->get ( 'Filters' );
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/indexing.css' );

		// Pagination view object model state populated
		$pagination = new JPagination ( $this->getModel ()->getState ( 'numpages', 10 ) * 10, $this->getModel ()->getState ( 'limitstart', 0 ), 10 );
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->serpsearch = $this->getModel ()->getState ( 'serpsearch' );
		$this->rankedpagekeyword = $this->getModel ()->getState ( 'rankedpagekeyword', null );
		$this->items = $rows;
		$this->lists = $lists;
		
		// Store number of indexed links found
		$totalLinksHtml = JMapSeostatsServicesGoogleSearch::$numberIndexedPages;
		if($totalLinksHtml && isset($totalLinksHtml[1])) {
			$explodedChunks = explode(' ', $totalLinksHtml[1]);
			if(JMapSeostatsServicesGoogleSearch::$paginationNumber > 0) {
				$calculatedChunk = count($explodedChunks) - 4;
			} else {
				$calculatedChunk = 1;
			}
			if(is_numeric(str_replace(',', '.', $explodedChunks[$calculatedChunk]))) {
				$this->totalPagesValue = $explodedChunks[$calculatedChunk];
			}
		}
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
			
		parent::display ( 'list' );
	}
}