<?php
// namespace administrator\components\com_jmap\views\sources;
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @since 1.0
 */
class JMapViewSources extends JMapView {
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_SITEMAP_DATA_NEW' : 'COM_JMAP_SITEMAP_DATA_EDIT';
	
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-data.png")}');
		JToolBarHelper::title( JText::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				// Evaluate data source type
				if($this->record->type === 'plugin') {
					JToolBarHelper::apply( 'sources.importPlugins', 'COM_JMAP_IMPORT_PLUGIN');
				} else { // All other cases
					JToolBarHelper::apply( 'sources.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'sources.saveEntity', 'JSAVE');
				}
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					JToolBarHelper::apply( 'sources.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'sources.saveEntity', 'JSAVE');
				}
			}
		}
			
		JToolBarHelper::custom('sources.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-data.png")}');
	
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_( 'COM_JMAP_SITEMAP_DATA' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			JToolBarHelper::addNew('wizard.display', 'COM_JMAP_NEW_SOURCE');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::editList('sources.editentity', 'COM_JMAP_EDIT_SOURCE');
		}
	
		JToolBarHelper::custom( 'sources.copyEntity', 'copy.png', 'copy_f2.png', 'COM_JMAP_DUPLICATE' );
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::deleteList(JText::_('COM_JMAP_DELETE_SOURCE'), 'sources.deleteentity');
		}
		
		if ($user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::custom('sources.exportEntities', 'download', 'download', 'COM_JMAP_EXPORT_SOURCE', true);
			JToolBarHelper::custom('sources.importEntities', 'upload', 'upload', 'COM_JMAP_IMPORT_SOURCE', false);
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
		
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/filesources.js' );
		$doc->addScriptDeclaration("function checkAll(n) {
				var form = jQuery('#adminForm');
				var checkItems = jQuery('input[type=checkbox][data-enabled!=false][name!=toggle]', form);
				if(!jQuery('input[type=checkbox][name=toggle]').prop('checked')) {
					jQuery(checkItems).prop('checked', false);
					jQuery('input[name=boxchecked]', form).val(0);
				} else {
					jQuery(checkItems).prop('checked', true);
					if(checkItems.length) {jQuery('input[name=boxchecked]', form).val(checkItems.length)};
				}
				
		};");
		$doc->addScriptDeclaration("
						Joomla.submitbutton = function(pressbutton) {
							Joomla.submitform( pressbutton );
							if (pressbutton == 'sources.exportEntities') {
								jQuery('#adminForm input[name=task]').val('sources.display');
							}
							return true;
						};
					");
		
		// Inject js translations
		$translations = array(
				'COM_JMAP_REQUIRED',
				'COM_JMAP_PICKFILE',
				'COM_JMAP_STARTIMPORT',
				'COM_JMAP_CANCELIMPORT'
		);
		$this->injectJsTranslations($translations, $doc);
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new JPagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		
		$this->user = JFactory::getUser ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->lists = $lists;
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->cParams = $this->getModel ()->getComponentParams();
		
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
		$arrayExclusion = array();
		for($wmo=1,$maxOperators=3;$wmo<=$maxOperators;$wmo++) {
			$arrayExclusion[] = 'where'.$wmo.'_operator_maintable';
			for($wmojt=1,$maxjtOperators=3;$wmojt<=$maxjtOperators;$wmojt++) {
				$arrayExclusion[] = 'where'.$wmojt.'_operator_jointable'.$wmo;
			}
		}
		JFilterOutput::objectHTMLSafe( $row->sqlquery_managed, ENT_QUOTES, $arrayExclusion);
		
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$base = JURI::root();
		$this->loadJQuery($doc);
		$this->loadJQueryUI($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/sources.css' );
		$doc->addScriptDeclaration("var jmap_baseURI='$base';");
		
		// Inject js translations
		$translations = array('COM_JMAP_SELECTFIELD',
							  'COM_JMAP_STORED_PRIORITY',
							  'COM_JMAP_ERROR_FOR_PRIORITY',
							  'COM_JMAP_DELETED_PRIORITY',
							  'COM_JMAP_PRIORITY_MAKE_SELECTIONS',
							  'COM_JMAP_VALIDATON_ERROR_NOPRIORITY',
							  'COM_JMAP_PRIORITY_CHOOSE_TO_DELETE',
							  'COM_JMAP_CATEGORIES_EXCLUSION',
							  'COM_JMAP_CATEGORIES_INCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION_DESC',
							  'COM_JMAP_CHOOSE_CATEGORIES_INCLUSION',
							  'COM_JMAP_CHOOSE_CATEGORIES_INCLUSION_DESC',
							  'COM_JMAP_ARTICLES_EXCLUSION',
							  'COM_JMAP_ARTICLES_INCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_EXCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_EXCLUSION_DESC',
							  'COM_JMAP_CHOOSE_ARTICLES_INCLUSION',
							  'COM_JMAP_CHOOSE_ARTICLES_INCLUSION_DESC',
							  'COM_JMAP_RAW_SOURCE_LINK',
							  'COM_JMAP_LINK_TITLE',
							  'COM_JMAP_LINK_HREF',
							  'COM_JMAP_SELECTED_LINK_RECORDS');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/sources.js' );
		$doc->addScriptDeclaration("
					Joomla.submitbutton = function(pressbutton) {
						if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
						}
				
						jQuery('#adminForm').validation();
				
						if (pressbutton == 'sources.cancelEntity') {
							jQuery('#adminForm').off();
							Joomla.submitform( pressbutton );
							return true;
						}
		
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
							return true;
						}
						return false;
					};
				");
		
		$lists = $this->getModel()->getLists($row);
		$this->hasManifest = $this->getModel()->getHasManifest($row);
		$this->hasItemsCategorization = $this->getModel()->getHasCategoryByTitle($row);
		$this->isCategorySource = $this->getModel()->getIsCategorySource($row);
		$this->hasRouteManifest = $this->getModel()->getHasRouteManifest($row);
		$this->hasCreatedDate = $this->getModel()->getHasCreatedDate($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->supportedGNewsExtension = $this->getModel()->getHasGNewsSupport($row);
		$this->supportedRSSExtension = $this->getModel()->getHasRSSSupport($row);
		
		// Load the parameter form if the data source is of type plugin
		if($row->type == 'plugin') {
			$this->params_form = $this->getModel()->getFormFields($row);
		}
		$this->record = $row;
		$this->lists = $lists;
		
		// Registry sqlquery_managed object
		$this->registrySqlQueryManaged = new JRegistry();
		$this->registrySqlQueryManaged->loadObject($row->sqlquery_managed);
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}