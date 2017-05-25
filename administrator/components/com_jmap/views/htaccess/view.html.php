<?php
// namespace administrator\components\com_jmap\views\htaccess;
/**
 * @package JMAP::HTACCESS::::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Htaccess editor view
 *
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @since 3.0
 */
class JMapViewHtaccess extends JMapView {
	/**
	 * Edit entity view
	 *
	 * @access public
	 * @param Object& $row the item to edit
	 * @return void
	 */
	public function editEntity(&$row) {
		// Load JS Client App dependencies
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$translations = array (	'COM_JMAP_HTACCESS_PATH',
								'COM_JMAP_HTACCESS_OLD_PATH',
								'COM_JMAP_HTACCESS_DIRECTIVE_ADDED',
								'COM_JMAP_HTACCESS_REQUIRED' );
		$this->injectJsTranslations($translations, $doc);

		// Load specific JS App
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/htaccess.css' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/htaccess.js' );

		$this->option = $this->option;
		$this->htaccessVersion = $this->getModel ()->getState ( 'htaccess_version' );
		$this->record = $row;
	
		parent::display ( 'edit' );
	}
}