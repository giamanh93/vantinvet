<?php
// namespace administrator\components\com_jmap\views\seospider;
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage seospider
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage seospider
 * @since 3.8
 */
class JMapViewSeospider extends JMapView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JMAP_SITEMAP_SEOSPIDER' ), 'jmap' );

		// Check user permissions to edit record
		if ($user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::custom('seospider.exportXls', 'download', 'download', 'COM_JMAP_EXPORT_XLS', false);
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
		// Tooltip for locked record
		JHTML::_('behavior.tooltip');
		
		// Get main records
		$rows = $this->get ( 'Data' );
		$lists = $this->get ( 'Filters' );
		$total = $this->get ( 'Total' );
		$this->cparams = $this->getModel()->getComponentParams();
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/seospider.js' );
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/seospider.css' );
		$doc->addScriptDeclaration("var jmap_baseURI='" . JUri::root() . "';");
		$doc->addScriptDeclaration("var jmap_crawlerDelay=" . $this->cparams->get('seospider_crawler_delay', 0) . ";");
		
		// Inject js translations
		$translations = array (
				'COM_JMAP_SEOSPIDER_TITLE',
				'COM_JMAP_SEOSPIDER_PROCESS_RUNNING',
				'COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION',
				'COM_JMAP_SEOSPIDER_ERROR_STORING_FILE',
				'COM_JMAP_SEOSPIDER_GENERATION_COMPLETE',
				'COM_JMAP_SEOSPIDER_CRAWLING_LINKS',
				'COM_JMAP_SEOSPIDER_NOAVAILABLE_LINK',
				'COM_JMAP_SEOSPIDER_LINKVALID',
				'COM_JMAP_SEOSPIDER_LINK_NOVALID',
				'COM_JMAP_SEOSPIDER_NOINFO',
				'COM_JMAP_SEOSPIDER_TITLE_TOOSHORT',
				'COM_JMAP_SEOSPIDER_TITLE_TOOSHORT_DESC',
				'COM_JMAP_SEOSPIDER_TITLE_TOOLONG',
				'COM_JMAP_SEOSPIDER_TITLE_TOOLONG_DESC',
				'COM_JMAP_SEOSPIDER_TITLE_MISSING',
				'COM_JMAP_SEOSPIDER_TITLE_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG_DESC',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_TITLE',
				'COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_DESCRIPTION',
				'COM_JMAP_SEOSPIDER_NOINDEX',
				'COM_JMAP_SEOSPIDER_NOINDEX_DESC',
				'COM_JMAP_SEOSPIDER_HEADERS_MISSING',
				'COM_JMAP_SEOSPIDER_HEADERS_MISSING_DESC',
				'COM_JMAP_SEOSPIDER_OPEN_DETAILS',
				'COM_JMAP_SEOSPIDER_TITLE_DETAILS',
				'COM_JMAP_SEOSPIDER_DESCRIPTION_DETAILS',
				'COM_JMAP_SEOSPIDER_SELECTED_LINK_DETAILS',
				'COM_JMAP_EXPORT_XLS'
		);
		$this->injectJsTranslations($translations, $doc);
						
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new JPagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = JFactory::getUser ();
		$this->pagination = $pagination;
		$this->link_type = $this->getModel ()->getState ('link_type', null);
		$this->searchpageword = $this->getModel ()->getState ('searchpageword', null);
		$this->dataRole = $this->cparams->get('linksanalyzer_indexing_analysis', 1) ? 'link' : 'neutral';
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		parent::display ( 'list' );
	}
}