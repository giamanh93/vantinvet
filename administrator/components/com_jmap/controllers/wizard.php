<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Controller for wizard creation of data sources
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage controllers
 * @since 2.0
 */
class JMapControllerWizard extends JMapController {
	/**
	 * Persist message for errors or user info after creation from JS app to server domain redirect
	 * 
	 * @access private
	 * @param session
	 * @return void
	 */
	private function persistMessageQueue($session) {
		// Persist messages if they exist for JS client post complete redirect
		$app = $this->app;
		$messageQueue = $app->getMessageQueue();
		if (count($messageQueue)) {
			$session = JFactory::getSession();
			$session->set('application.queue', $messageQueue);
		}
	}

	/**
	 * Display main wizard creation panel for supported extensions
	 * 
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Parent construction and view display
		parent::display($cachable);
	}

	/**
	 * Create entity task to start process for selected extension
	 * 
	 * @access public
	 * @return void
	 */
	public function createEntity() {
		$client = $this->app->input->get('client', 'default');
		$targetExtension = $this->app->input->get('extension');
		$option = $this->option;
		if (!$targetExtension) {
			// Target extension not specified
			$this->setRedirect("index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_NOTARGET_EXTENSION'));
			return false;
		}
		// Get sources model and make dependency injection
		$sourcesModel = $this->getModel('sources');

		// Get core MVC model
		$model = $this->getModel(null, null, array('extension' => $targetExtension, 'sourcesModel' => $sourcesModel));

		// Call create entity process start into model
		if (!$model->createEntityProcess()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());

			// Switch client if JS APP halt execution of application on exceptions detected
			switch ($client) {
			case 'jsapp':
				$this->app->enqueueMessage (JText::_('COM_JMAP_ERROR_CREATING_DATASOURCE'), 'message');
				$this->persistMessageQueue();
				return false;
				break;

			case 'default';
			default;
			// Switch client user controller make redirect to wizard.display on exceptions detected
				$this->setRedirect("index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_CREATING_DATASOURCE'));
				return false;
			}
		}

		// Successfully process terminated with no errors
		switch ($client) {
		case 'jsapp':
			$this->app->enqueueMessage(sprintf(JText::_('COM_JMAP_SUCCESS_CREATING_DATASOURCE'), ucfirst(str_replace('_', ' ', $targetExtension))), 'message');
			$this->persistMessageQueue();
			return false;
			break;

		case 'default';
		default;
			// Switch client user controller make redirect to wizard.display on exceptions detected
			$this->setRedirect("index.php?option=$option&task=sources.display", sprintf(JText::_('COM_JMAP_SUCCESS_CREATING_DATASOURCE'), ucfirst(str_replace('_', ' ', $targetExtension))));
			return true;
		}
	}
}