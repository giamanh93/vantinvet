<?php
// namespace administrator\components\com_jmap\views\pingomatic;
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @since 2.0
 */
class JMapViewPingomatic extends JMapView {
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
		$toolbarHelperTitle = $isNew ? 'COM_JMAP_PINGOMATIC_LINKS_NEW' : 'COM_JMAP_PINGOMATIC_LINKS_EDIT';
		
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-pingomatic.png")}');
		JToolBarHelper::title( JText::_( $toolbarHelperTitle ), 'jmap' );
	
		if ($isNew)  {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create', 'com_jmap'))) {
				JToolBarHelper::custom('pingomatic.sendEntity', 'broadcast', 'broadcast', 'COM_JMAP_SEND_PING', false);
				JToolBarHelper::apply( 'pingomatic.applyEntity', 'JAPPLY');
				JToolBarHelper::save( 'pingomatic.saveEntity', 'JSAVE');
			}
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($user->authorise('core.edit', 'com_jmap')) {
					JToolBarHelper::custom('pingomatic.sendEntity', 'broadcast', 'broadcast', 'COM_JMAP_SEND_PING', false);
					JToolBarHelper::apply( 'pingomatic.applyEntity', 'JAPPLY');
					JToolBarHelper::save( 'pingomatic.saveEntity', 'JSAVE');
				}
			}
		}
			
		JToolBarHelper::custom('pingomatic.cancelEntity', 'cancel', 'cancel', 'JCANCEL', false);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-pingomatic.png")}');
	
		$user = JFactory::getUser();
		JToolBarHelper::title( JText::_('COM_JMAP_PINGOMATIC' ), 'jmap' );
		// Access check.
		if ($user->authorise('core.create', 'com_jmap')) {
			JToolBarHelper::addNew('pingomatic.editentity', 'COM_JMAP_NEW_LINK');
		}
	
		if ($user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::editList('pingomatic.editentity', 'COM_JMAP_EDIT_LINK');
		}
	
		if ($user->authorise('core.delete', 'com_jmap') && $user->authorise('core.edit', 'com_jmap')) {
			JToolBarHelper::deleteList('COM_JMAP_DELETE_LINK', 'pingomatic.deleteentity');
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
		$this->loadJQueryUI($doc);
		$doc->addScriptDeclaration("
						jQuery(function($) {
							$('input[data-role=calendar]').datepicker({
								dateFormat:'yy-mm-dd'
							}).prev('span').on('click', function(){
								$(this).datepicker('show');
							});
							$('a.fancybox').fancybox();
						});
					");
		$doc->addStyleDeclaration('#limit{margin-top:30px}');
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/jquery.fancybox.css' );
		$doc->addCustomTag ('<script type="text/javascript" src="' . JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery.fancybox.pack.js' . '"></script>');
		
		$orders = array ();
		$orders ['order'] = $this->getModel ()->getState ( 'order' );
		$orders ['order_Dir'] = $this->getModel ()->getState ( 'order_dir' );
		// Pagination view object model state populated
		$pagination = new JPagination ( $total, $this->getModel ()->getState ( 'limitstart' ), $this->getModel ()->getState ( 'limit' ) );
		$dates = array('from'=>$this->getModel()->getState('fromPeriod'), 'to'=>$this->getModel()->getState('toPeriod'));
		
		$this->user = JFactory::getUser ();
		$this->pagination = $pagination;
		$this->searchword = $this->getModel ()->getState ( 'searchword' );
		$this->orders = $orders;
		$this->items = $rows;
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->dates = $dates;
		
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
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/pingomatic.css' );
		$doc->addScriptDeclaration("var jmap_baseURI='$base';");
		$doc->addScriptDeclaration("var jmap_urischeme='$this->urischeme';");
		
		// Inject js translations
		$translations = array(	'COM_JMAP_SELECTFIELD',
							  	'COM_JMAP_SELECTONESERVICE',
						  		'COM_JMAP_PROGRESSPINGOMATICTITLE',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE2SUCCESS',
							  	'COM_JMAP_PROGRESSPINGOMATICSUBTITLE2ERROR',
							  	'COM_JMAP_PROGRESSMODELTITLE',
								'COM_JMAP_PROGRESSMODELSUBTITLE',
								'COM_JMAP_PROGRESSMODELSUBTITLE2SUCCESS',
								'COM_JMAP_PROGRESSMODELSUBTITLE2ERROR',
								'COM_JMAP_LOADING');
		$this->injectJsTranslations($translations, $doc);
		
		// Load specific JS App
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery.form.min.js' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/pingomatic.js' );
		$doc->addScriptDeclaration("
						Joomla.submitbutton = function(pressbutton) {
							if(!jQuery.fn.validation) {
								jQuery.extend(jQuery.fn, jmapjQueryBackup.fn);
							}
				
							jQuery('#adminForm').validation();
							
							if (pressbutton == 'pingomatic.cancelEntity') {	
								jQuery('#adminForm').off();
								Joomla.submitform( pressbutton );
								return true;
							}
							
							if (pressbutton == 'pingomatic.sendEntity') {	
								// Start Pingomatic JS APP plugin
								jQuery('#adminForm').Pingomatic({});
								return false;
							}
							
							if(jQuery('#adminForm').validate()) {
								Joomla.submitform( pressbutton );
								return true;
							}
							return false;
						};
					");
		
		$lists = $this->getModel()->getLists($row);
		$this->option = $this->getModel ()->getState ( 'option' );
		$this->record = $row;
		$this->lists = $lists;
		
		// Aggiunta toolbar
		$this->addEditEntityToolbar();
		
		parent::display ( 'edit' );
	}
}