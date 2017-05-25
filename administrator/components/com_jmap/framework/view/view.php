<?php
// namespace administrator\components\com_jmap\framework\view;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage view
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.application.component.view' );
jimport ( 'joomla.html.pagination' );
 
/**
 * Base view for all display core
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage view
 * @since 2.0
 */
class JMapView extends JViewLegacy {
	/**
	 * User object for ACL authorise check
	 *
	 * @access protected
	 * @var Object
	 */
	protected $user;
	
	/**
	 * Document object, needed by views to inject
	 * CSS/JS tags into document output
	 *
	 * @access public
	 * @var Object
	 */
	public $document;
	
	/**
	 * Reference to option executed
	 *
	 * @access public
	 * @var string
	 */
	public $option;
	
	/**
	 * Reference to application
	 *
	 * @access public
	 * @var Object
	 */
	public $app;
	
	/**
	 * Find the field flagged to be used as category title from that chosen in the select field
	 * in one of the valid jointable for a single user defined data source
	 * 
	 * @access protected
	 * @param Object $source
	 * @return string The field string to use as title for categorization
	 */
	protected function findAsCategoryTitleField($source) {
		// ****JOIN TABLES PROCESSING****
		for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++) {
			// Main base condition: 4 fields all compiled otherwise continue
			if(	empty($source->chunks->{'table_joinfrom_jointable'.$jt}) ||
				empty($source->chunks->{'table_joinwith_jointable'.$jt}) ||
				empty($source->chunks->{'field_joinfrom_jointable'.$jt}) ||
				empty($source->chunks->{'field_joinwith_jointable'.$jt})) {
				continue;
			}
			if(!empty($source->chunks->{'field_select_jointable'.$jt})) {
				$objectProperty = $source->chunks->{'field_select_jointable'.$jt};
				$objectProperty = !empty($source->chunks->{'field_as_jointable'.$jt}) ? $source->chunks->{'field_as_jointable'.$jt} : $objectProperty;
				if(!empty($source->chunks->{'use_category_title_jointable'.$jt}) && !!$source->chunks->{'use_category_title_jointable'.$jt}) {
					return $objectProperty;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Inject language constant into JS Domain maintaining same name mapping
	 * 
	 * @access protected
	 * @param $translations Object&
	 * @param $document Object&
	 * @return void
	 */
	protected function injectJsTranslations(&$translations, &$document) {
		$jsInject = null;
 		// Do translations
		foreach ( $translations as $translation ) {
			$jsTranslation = strtoupper ( $translation );
			$translated = JText::_( $jsTranslation, true);
			$jsInject .= <<<JS
				var $translation = '{$translated}'; 
JS;
		}
		$document->addScriptDeclaration($jsInject);
	}
	
	/**
	 * Manage injecting jQuery framework into document with class inheritance support
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadJQuery(&$document) {
		try { JHtml::_('behavior.core'); } catch (Exception $e){} // Compatibility fix ensured for Joomla 3.4+
		// jQuery foundation framework and class support
		JHTML::_('bootstrap.framework');
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/classnative.js' );
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/jstorage.min.js' );
	}
	
	/**
	 * Manage injecting Bootstrap framework into document
	 * 
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadBootstrap(&$document) {
		$document->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/bootstrap-override.css' );
		// Main styles for JSitemap admin interface
		$document->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/bootstrap-interface.css' );
		
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/bootstrap-override.js' );
		// Main JS file for JSitemap admin interface
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/bootstrap-interface.js' );
	}
	
	/**
	 * Manage injecting valildation plugin into document
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadValidation(&$document) {
		$document->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/simplevalidation.css' );
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery.simplevalidation.js' );
	}
	
	/**
	 * Manage injecting jQuery UI framework into document
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadJQueryUI(&$document) {
		$document->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/jqueryui/jquery-ui.custom.min.css' );
		$document->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/jquery-ui.min.js' );
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $config
	 *        	return Object
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
	
		$this->app = JFactory::getApplication ();
		$this->user = JFactory::getUser ();
		$this->document = JFactory::getDocument();
		$this->option = $this->app->input->get ( 'option' );
	}
}