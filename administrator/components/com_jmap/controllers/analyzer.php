<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main sitemap analyzer controller manager
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage controllers
 * @since 2.3.3
 */
class JMapControllerAnalyzer extends JMapController {
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
		if($this->app->input->get('jsclient', false)) {
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
		$filter_type = $this->getUserStateFromRequest ( "$option.$scope.filterstate", 'filter_type', null );
		$filter_order = $this->getUserStateFromRequest ( "$option.$scope.filter_order", 'filter_order', '', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', '', 'word' );
		parent::setModelState ( $scope, false );
		
		// Set model state
		$defaultModel->setState ( 'sitemaplang', $sitemapLang );
		$defaultModel->setState ( 'sitemapdataset', $sitemapDataset );
		$defaultModel->setState ( 'sitemapitemid', $sitemapItemid );
		$defaultModel->setState ( 'link_type', $filter_type );
		$defaultModel->setState ( 'order', $filter_order );
		$defaultModel->setState ( 'order_dir', $filter_order_Dir );
		
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
		$defaultModel = $this->setModelState('analyzer');
		 
		// Parent construction and view display
		parent::display($cachable);
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