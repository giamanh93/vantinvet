<?php 
//namespace components\com_jmap\controllers; 
/** 
 * @package JMAP::AJAXSERVER::components::com_jmap 
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C)2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/** 
 * Controller for tasks logic API ENDPOINT purpouse
 * @package JMAP::AJAXSERVER::components::com_jmap  
 * @subpackage controllers
 * @since 1.0
 */  
class JMapControllerAjaxserver extends JMapController { 
	/**
	 * AS SMVC entity here we treat HTTP request and identifier map
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Id entità risposta ajax che identifica il subtask da eseguire in questo caso
		$params = json_decode($this->app->input->getString('data', null));
		
		// Load additional models and make Dependency Injection thanks to JS controls
		$DIModels = @$params->DIModels;
		$models = array();
		if(!empty($DIModels)) {
			foreach ($DIModels as $DIModel) {
				if($DIModel->modelside != $this->app->getClientId()) {
					// Add extra include paths
					JModel::addIncludePath(JPATH_COMPONENT_SITE . 'models/');
				}
				$models[$DIModel->modelname] = $this->getModel ($DIModel->modelname);
			} 
		}
		// This model maps Remote Procedure Call
		$model = $this->getModel ();
		$userData = $model->loadAjaxEntity ($params->idtask, $params->param, $models);
		
	 	// Format response for JS client as requested
	 	$document = JFactory::getDocument();
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		
		$view =  $this->getView ( $coreName, $viewType, '', array ('base_path' => $this->basePath ) );
		$view->display ($userData);
	} 
}