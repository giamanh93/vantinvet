<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * CPanel controller
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class JMapControllerCpanel extends JMapController {
	/**
	 * Show Control Panel
	 * @access public
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		$defaultModel = $this->getModel();
		$defaultModel->setState('option', $this->option);
		// Auto-Refresh menu sources
		if(!$defaultModel->syncMenuSources()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $defaultModel->getErrors();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getErrorLevel());
			}
		}
		
		// Retrieve status and version informations
		$view = $this->getView();
		$HTTPClient = new JMapHttp();
		$view->set('httpclient', $HTTPClient);
		
		parent::display ($cachable); 
	}
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function editEntity() {
		$this->app->input->set('tmpl', 'component');
		$option = $this->option;
		
		$model = $this->getModel();
		$model->setState('option', $option);
	
		// Try to load record from model
		if(!$robotsContent = $model->loadEntity(null)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getErrorLevel());
			}
			return false;
		}
	
		// Access check
		if(!$this->allowEdit($model->getState('option'))) {
			$this->app->enqueueMessage(JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Get view and pushing model
		$view = $this->getView();
		$view->setModel ( $model, true );
	
		// Call edit view
		$view->editEntity($robotsContent);
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$option = $this->option;
		$data = $this->app->input->getString('robots_contents', null);
	
		//Load della  model e bind store
		$model = $this->getModel ();
	
		if(!$result = $model->storeEntity($data)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ("index.php?option=$option&task=cpanel.editEntity");
			return false;
		}

		$this->setRedirect ( "index.php?option=$option&task=cpanel.editEntity", JText::_('COM_JMAP_SUCCESS_SAVE_ROBOTS'));
	}
		
	/**
	 * Show suggestions
	 * @access public
	 * @return void
	 */
	public function installerApp() {
		$view = $this->getView();
	
		$view->showInstallerApp();
	}
	
	/**
	 * Retrieve status info from external module
	 * @access public
	 * @return void
	 */
	public function getUpdates() {
		$HTTPClient = new JMapHttp();

		// Model instance
		$model = $this->getModel();
		$model->setState('option', 'com_jmap');
		$jsonObject = $model->getUpdates($HTTPClient);

		header('Content-type: application/json');
		echo json_encode($jsonObject);
	}
}