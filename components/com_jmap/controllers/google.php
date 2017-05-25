<?php
// namespace components\com_jmap\controllers;
/**
 * @package JMAP::GOOGLE::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller
 * @package JMAP::GOOGLE::components::com_jmap
 * @subpackage controllers
 * @since 3.5
 */
class JMapControllerGoogle extends JMapController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return void
	 */
	protected function setModelState($scope = 'default', $ordering = true) {
		$option = $this->option;
		
		// Get default model
		$defaultModel = $this->getModel();
		
		// Set model state
		$defaultModel->setState ( 'option', $option );
		$defaultModel->setState ( 'googlestats', 'analytics');
		
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JMapModel' );
		
		$this->setModelState('google');
		parent::display($cachable, $urlparams);
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return void
	 */
	public function deleteEntity() {
		// Mixin, add include path for admin side to avoid DRY on model
		$this->addModelPath ( JPATH_COMPONENT_ADMINISTRATOR . '/models', 'JMapModel' );
		
		// Load della model e checkin before exit
		$model = $this->getModel ();

		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display", JText::_ ( 'COM_JMAP_GOOGLEANALYTICS_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( JRoute::_("index.php?option=" . $this->option . "&view=" . $this->corename), JText::_ ( 'COM_JMAP_GOOGLEANALYTICS_SUCCESS_LOGOUT' ) );
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );

		// Manage partial language translations from the backend side
		$jLang = JFactory::getLanguage ();
		$jLang->load ( 'com_jmap', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true );
		if ($jLang->getTag () != 'en-GB') {
			$jLang->load ( 'com_jmap', JPATH_ADMINISTRATOR, null, true, false );
			$jLang->load ( 'com_jmap', JPATH_COMPONENT_ADMINISTRATOR, null, true, false );
		}
		
		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/framework/composer/autoload_real.php';
		ComposerAutoloaderInitfc5c9af51413a149e4084a610a3ab6de::getLoader();
	}
}