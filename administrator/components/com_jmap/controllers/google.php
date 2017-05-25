<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller
 * @package JMAP::GOOGLE::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.1
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
		
		// Set calendar period for search console stats
		$defaultStartPeriod = date ( "Y-m-01", strtotime ( date ( "Y-m-d" ) ) );
		$defaultEndPeriod = date ( "Y-m-d", strtotime ( "-1 day", strtotime ( "+1 month", strtotime ( date ( "Y-m-01" ) ) ) ) );
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod', strval($defaultStartPeriod));
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod', strval($defaultEndPeriod));
		
		// Set model state
		$defaultModel->setState ( 'option', $option );
		$defaultModel->setState ( 'googlestats', $this->app->input->getCmd('googlestats', 'analytics'));
		$defaultModel->setState('fromPeriod', $fromPeriod);
		$defaultModel->setState('toPeriod', $toPeriod);
		
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
		// Access check.
		if (!$this->user->authorise ( 'jmap.google', $this->option )) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		
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
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Origin stats type
		$origin = $this->app->input->get('googlestats', null);
		if($origin == 'webmasters') {
			$this->option .= '&googlestats=webmasters';
		}

		if (! $model->deleteEntity ( null )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display", JText::_ ( 'COM_JMAP_GOOGLE_ERROR_' . 'LOGOUT' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display", JText::_ ( 'COM_JMAP_GOOGLE_SUCCESS_LOGOUT' ) );
	}
	
	/**
	 * Submit a sitemap using the GWT API
	 *
	 * @access public
	 * @return void
	 */
	public function submitSitemap() {
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Retrieve the sitemap link
		$sitemapUri = $this->app->input->getString('sitemaplink');
		if(!$sitemapUri) {
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_MISSING_DATA' ) );
			return false;
		}
	
		if (! $model->submitSitemap ( $sitemapUri )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERROR_SUBMITTING_SITEMAP' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_SITEMAP_SUBMITTED' ) );
	}
	
	/**
	 * Delete a sitemap using the GWT API
	 *
	 * @access public
	 * @return void
	 */
	public function deleteSitemap() {
		// Load della model e checkin before exit
		$model = $this->getModel ();

		// Retrieve the sitemap link
		$sitemapUri = $this->app->input->getString('sitemapurl');
	
		if (! $model->deleteSitemap ( $sitemapUri )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERROR_DELETING_SITEMAP' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_SITEMAP_DELETED' ) );
	}
	
	/**
	 * Delete a sitemap using the GWT API
	 *
	 * @access public
	 * @return void
	 */
	public function markAsFixed() {
		// Load della model e checkin before exit
		$model = $this->getModel ();
	
		// Retrieve the sitemap link
		$crawlErrorsCategory = $this->app->input->getCmd('crawlerrors_category');
	
		if (! $model->markAsFixed ( $crawlErrorsCategory )) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError ( null, false );
			$this->app->enqueueMessage ( $modelException->getMessage (), $modelException->getErrorLevel () );
			$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERROR_FIXING_ERRORS' ) );
			return false;
		}
	
		$this->setRedirect ( "index.php?option=" . $this->option . "&task=" . $this->corename . ".display&googlestats=webmasters", JText::_ ( 'COM_JMAP_GOOGLE_WEBMASTERS_ERRORS_FIXED' ) );
	}
	
	/**
	 * Class Constructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );

		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/framework/composer/autoload_real.php';
		ComposerAutoloaderInitfc5c9af51413a149e4084a610a3ab6de::getLoader();
	}
}