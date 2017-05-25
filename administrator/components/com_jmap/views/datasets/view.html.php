<?php
// namespace administrator\components\com_jmap\views\datasets;
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @since 2.0
 */
class JMapViewDatasets extends JMapView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addEditEntityToolbar() {
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->record->id == 0);
		$checkedOut	= !($this->record->checked_out == 0 || $this->record->checked_out == $userId);
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_DATASETS_NEW' : 'COM_JMAP_DATASETS_EDIT';
		
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-datasets.png")}');
		JToolBarHelper::title( JText::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				JToolBarHelper::apply( 'datasets.applyEntity', 'JAPPLY');
				JToolBarHelper::save( 'datasets.saveEntity', 'JSAVE');
				JToolBarHelper::save2new( 'datasets.saveEntity2New');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					JToolBarHelper::apply( 'datasets.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'datasets.saveEntity', 'JSAVE');
					JToolBarHelper::save2new( 'datasets.saveEntity2New');
				}
			}
		}
			
		JToolBarHelper::custom('datasets.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-datasets.png")}');
	
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_('COM_JMAP_DATASETS' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			JToolBarHelper::addNew('datasets.editentity', 'COM_JMAP_NEW_DATASET');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::editList('datasets.editentity', 'COM_JMAP_EDIT_DATASET');
		}
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::deleteList('COM_JMAP_DELETE_LINK', 'datasets.deleteentity');
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
		$total = $this->get ( 'Total' );
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/datasets.css' );

		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new JPagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = JFactory::getUser ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
			
		parent::display ( 'list' );
	}
	
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Sanitize HTML Object2Form
		JFilterOutput::objectHTMLSafe( $row );
		
		// Detect uri scheme
		$instance = JUri::getInstance();
		$this->urischeme = $instance->isSSL() ? 'https' : 'http';
		
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$base = JURI::root();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/datasets.css' );
		$doc->addScriptDeclaration("var jmap_baseURI='$base';");
		$doc->addScriptDeclaration("var jmap_urischeme='$this->urischeme';");
		
		// Inject js translations
		$translations = array( 'COM_JMAP_SELECTONESOURCE' );
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery.form.min.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/datasets.js' );
		$doc->addScriptDeclaration("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
							
							if (pressbutton == 'datasets.cancelEntity') {
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
				
							if(jQuery('#adminForm').validate() && JMapDatasets.validateSelectable()) {
								Joomla.submitform( pressbutton );
								return true;
							}
							return false;
						};
					");
		
		$lists = $this->getModel()->getLists($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->componentParams = $this->getModel()->getComponentParams();
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}