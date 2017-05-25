<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main metainfo controller manager
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.2
 */
class JMapControllerMetainfo extends JMapController {
	/**
	 * Set model state from session userstate
	 * 
	 * @access protected
	 * @param string $scope        	
	 * @return void
	 */
	protected function setModelState($scope = 'default', $ordering = true) {
		$option = $this->option;
		
		// Get default model
		$defaultModel = $this->getModel ();
		
		// JS Client check and reset userstate
		if($this->app->input->get('metainfojsclient', false)) {
			$postedLang = $this->app->input->get('sitemaplang', null);
			if(!$postedLang) {
				$this->app->setUserState ( "$option.$scope.sitemaplang", null, '' );
			}
			$postedDataset = $this->app->input->get('sitemapdataset', null);
			if(!$postedDataset) {
				$this->app->setUserState ( "$option.$scope.sitemapdataset", null, '' );
			}
			$postedItemid = $this->app->input->get('sitemapitemid', null);
			if(!$postedItemid) {
				$this->app->setUserState ( "$option.$scope.sitemapitemid", null, '' );
			}
		}
		$sitemapLang = $this->getUserStateFromRequest ( "$option.$scope.sitemaplang", 'sitemaplang', '' );
		$sitemapDataset = $this->getUserStateFromRequest ( "$option.$scope.sitemapdataset", 'sitemapdataset', '' );
		$sitemapItemid = $this->getUserStateFromRequest ( "$option.$scope.sitemapitemid", 'sitemapitemid', '' );
		$searchPageWord = $this->getUserStateFromRequest ( "$option.$scope.searchpageword", 'searchpage', null, 'none', false );
		$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', '', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', '', 'word' );
		$filter_state = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_state', null );
		$filter_excludestate = $this->getUserStateFromRequest ( "$option.$scope.filterexcludestate", 'filter_excludestate', null );
		$exactsearchpage = $this->app->input->getInt ('exactsearchpage', null );
		
		parent::setModelState ( $scope, false );
		
		// Set model state
		$defaultModel->setState ( 'sitemaplang', $sitemapLang );
		$defaultModel->setState ( 'sitemapdataset', $sitemapDataset );
		$defaultModel->setState ( 'sitemapitemid', $sitemapItemid );
		$defaultModel->setState ( 'searchpageword', $searchPageWord );
		$defaultModel->setState ( 'exactsearchpage', $exactsearchpage );
		$defaultModel->setState ( 'order', $filter_order );
		$defaultModel->setState ( 'order_dir', $filter_order_Dir );
		$defaultModel->setState ( 'state', $filter_state );
		$defaultModel->setState ( 'excludestate', $filter_excludestate );
		
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
	function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState('metainfo');
		 
		// Parent construction and view display
		parent::display($cachable, $urlparams = false);
	}

	/**
	 * Export metainfo as CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntities() {
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=metainfo.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		// Set model state
		$defaultModel = $this->setModelState('metainfo');
		
		$viewType = $this->document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = $this->app->input->get ( 'layout', 'default' );
	
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
	
		// Push the model into the view (as default)
		$view->setModel ( $defaultModel, true );
	
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ('export');
	}
	
	/**
	 * Import metainfo from CSV data
	 *
	 * @access public
	 * @return void
	 */
	public function importEntities() {
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=metainfo.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Get the file manager instance with db connector dependency injection
		$filesManager = new JMapFileMetainfo(JFactory::getDbo(), $this->app);
	
		if(!$filesManager->import()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=metainfo.display", JText::_('COM_JMAP_METAINFO_ERROR_IMPORT'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=metainfo.display", JText::_('COM_JMAP_METAINFO_SUCCESS_IMPORT'));
	}
	
	/**
	 * Class Constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
	}
}