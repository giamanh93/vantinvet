<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Controller for Indexing of site links
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.3
 */
class JMapControllerIndexing extends JMapController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return void
	 */
	protected function setModelState($scope = 'default', $ordering = true) {
		$option = $this->option;
	
		$this->app->input->set ("limit", 10);
		$defaultModel = parent::setModelState($scope, false);
	
		// Get request state
		$numpages = $this->getUserStateFromRequest( "$option.$scope.numpages", 'numpages', 10, 'int' );
		$acceptLanguage = $this->getUserStateFromRequest( "$option.$scope.acceptlanguage", 'acceptlanguage' );
		$contryTLDLanguage = $this->getUserStateFromRequest( "$option.$scope.countriestld", 'countriestld' );
		
		// Set model ordering state
		$defaultModel->setState('numpages', $numpages);
		$defaultModel->setState('acceptlanguage', $acceptLanguage);
		$defaultModel->setState('countriestld', $contryTLDLanguage);
	
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
		// Set model state
		$defaultModel = $this->setModelState('indexing');
		
		// Parent construction and view display
		parent::display($cachable);
	}
	
	/**
	 * 
	 * Class Constructor
	 * 
	 * @access public
	 * @param $config
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		
		// Register Extra tasks
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'saveEntity2New', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
	}
}
?>