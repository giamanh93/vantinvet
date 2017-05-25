<?php
// namespace administrator\components\com_jmap\controllers;
/**
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Config controller concrete implementation
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class JMapControllerConfig extends JMapController {

	/**
	 * Show configuration
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Access check.
		if (!$this->allowAdmin($this->option)) {
			$this->setRedirect('index.php?option=com_jmap&task=cpanel.display', JTEXT::_('COM_JMAP_ERROR_ALERT_NOACCESS'));
			return false;
		}
		parent::display($cachable);
	}

	/**
	 * Save config entity
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		$model = $this->getModel();
		
		if(!$model->storeEntity()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( 'index.php?option=com_jmap&task=config.display', JText::_('COM_JMAP_ERROR_SAVING_PARAMS'));
			return false;
		}
		$this->setRedirect( 'index.php?option=com_jmap&task=config.display', JText::_('COM_JMAP_SAVED_PARAMS'));
	}
}
?>