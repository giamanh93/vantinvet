<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Htaccess manager controller
 *
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage controllers
 * @since 3.0
 */
class JMapControllerHtaccess extends JMapController {
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
		if(!$htaccessContent = $model->loadEntity(null)) {
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
		$view->editEntity($htaccessContent);
	}
	
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$option = $this->option;
		$data = $this->app->input->get('htaccess_contents', null, 'raw');
		$restored = $this->app->input->getInt('restored', 0);
	
		//Load della  model e bind store
		$model = $this->getModel ();
	
		if(!$result = $model->storeEntity($data)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ("index.php?option=$option&task=htaccess.editEntity");
			return false;
		}

		$saveMsg = $restored ? JText::_('COM_JMAP_SUCCESS_RESTORE_HTACCESS') : JText::_('COM_JMAP_SUCCESS_SAVE_HTACCESS');
		$this->setRedirect ( "index.php?option=$option&task=htaccess.editEntity", $saveMsg);
	}
}