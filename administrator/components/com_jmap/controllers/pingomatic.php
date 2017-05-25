<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Controller for Pingomatic links entity tasks
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage controllers
 * @since 2.0
 */
class JMapControllerPingomatic extends JMapController {
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
		
		$fromPeriod = $this->getUserStateFromRequest( "$option.$scope.fromperiod", 'fromperiod');
		$toPeriod = $this->getUserStateFromRequest( "$option.$scope.toperiod", 'toperiod');
		parent::setModelState($scope, false);
		
		$filter_order = $this->getUserStateFromRequest( "$option.$scope.filter_order", 'filter_order', 's.lastping', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest("$option.$scope.filter_order_Dir", 'filter_order_Dir', 'desc', 'word');

		$defaultModel->setState('fromPeriod', $fromPeriod);
		$defaultModel->setState('toPeriod', $toPeriod);
		$defaultModel->setState('order', $filter_order);
		$defaultModel->setState('order_dir', $filter_order_Dir);
		
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
		$document = $this->document;
		
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = $this->app->input->get ( 'layout', 'default' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
		
		// Set model state
		$model = $this->setModelState('pingomatic');
		
		// Push the model into the view (as default)
		$view->setModel ( $model, true );
		
		// If view type is raw instance and set dependency on view for HTTP client
		if($viewType == 'raw') {
			$view = $this->getView();
			$HTTPClient = new JMapHttp();
			$view->set('httpclient', $HTTPClient);
		}
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ();
	}

	/**
	 * Edit entity
	 * 
	 * @access public
	 * @return void
	 */
	public function editEntity() {
		$this->app->input->set('hidemainmenu', 1);
		$option = $this->option;
		$cid = $this->app->input->get ( 'cid', array (
				0
		), 'array' );
		$idEntity = ( int ) $cid [0];
		$user = $this->user;
		
		$model = $this->getModel();
		$model->setState('option', $option);
		
		// Try to load record from model 
		if(!$record = $model->loadEntity($idEntity)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getErrorLevel());
			}
			$this->setRedirect ( 'index.php?option=com_jmap&task=pingomatic.display');
			return false;
		}
		
		// Check out control on record
		if ($record->checked_out && $record->checked_out != $user->id) {
			$this->setRedirect ( 'index.php?option=' . $option . '&task=pingomatic.display', JText::_('COM_JMAP_CHECKEDOUT_RECORD'), 'notice');
			return false;
		}
		
		// Access check
		if($record->id && !$this->allowEdit($model->getState('option'))) {
			$this->setRedirect('index.php?option=' . $option . '&task=pingomatic.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		if(!$record->id && !$this->allowAdd($model->getState('option'))) {
			$this->setRedirect('index.php?option=' . $option . '&task=pingomatic.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		// Check out del record
		if ($record->id) {
			$record->checkout ( $user->id );
		}
		
		// Get view and pushing model
		$view = $this->getView();
		$view->setModel ( $model, true );
		
		// Call edit view
		$view->editEntity($record); 
	}

	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$task = $this->task;
		$option = $this->option;
		$context = implode('.', array($option, strtolower($this->getNames()), 'errordataload'));
		
		// Security layer for tags html outputted fields
		$sanitizedFields = array('title', 'blogurl', 'rssurl');
		foreach ($sanitizedFields as $field) {
			$this->requestArray[$this->requestName][$field] = strip_tags($this->requestArray[$this->requestName][$field]);
		}
		
		//Load della  model e bind store
		$model = $this->getModel ();
		
		if(!$result = $model->storeEntity()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			
			// Store data for session recover
			$this->app->setUserState($context, $this->requestArray[$this->requestName]);
			$this->setRedirect ( 'index.php?option=com_jmap&task=pingomatic.editEntity&cid[]=' . $this->app->input->get ( 'id' ), JText::_('COM_JMAP_ERROR_SAVING'));
			return false;
		}

		// Security safe if not model record id detected
		if(!$id = $result->id) {
			$id = $this->app->input->get ( 'id' );
		}
		
		$redirects = $task == 'saveEntity' ? array('task'=>'display', 'msgsufix'=>'_SAVING') : array('task'=>'editEntity&cid[]=' . $id, 'msgsufix'=>'_APPLY');
		$msg = 'COM_JMAP_SUCCESS' . $redirects['msgsufix'];
		$controllerTask = $redirects['task'];
	
		$this->setRedirect ( "index.php?option=$option&task=pingomatic.$controllerTask", JText::_($msg));
	}

	/**
	 * Manage cancel edit for entity and unlock record checked out
	 * 
	 * @access public
	 * @return void
	 * @ReturnType void
	 */
	public function cancelEntity() {
		$id = $this->app->input->get ( 'id' );
		$option = $this->option;
		//Load della  model e checkin before exit
		$model = $this->getModel ( );
		
		if(!$model->cancelEntity($id)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
		}
		 
		$this->setRedirect ( "index.php?option=$option&task=pingomatic.display", JText::_('COM_JMAP_CANCELED_OPERATION') );
	}

	/**
	 * Delete a db table entity
	 * 
	 * @access public
	 * @return void
	 * @ReturnType void
	 */
	public function deleteEntity() {
		$cids = $this->app->input->get ( 'cid', array (), 'array' );
		$option = $this->option;
		// Access check
		if(!$this->allowDelete($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=pingomatic.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		//Load della  model e checkin before exit
		$model = $this->getModel ( );
		
		if(!$model->deleteEntity($cids)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=pingomatic.display", JText::_('COM_JMAP_ERROR_DELETE'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=pingomatic.display", JText::_('COM_JMAP_SUCCESS_DELETE') );
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
	}
}
?>