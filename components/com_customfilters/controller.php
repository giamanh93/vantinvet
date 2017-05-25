<?php
/**
 *
 * Customfilters basic controller
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: customfilters.php 1 2014-02-19 12:29:00Z sakis $
 */

// no direct access
defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

class CustomfiltersController extends JControllerLegacy{
	protected $default_view='products';

	function __construct($config = array()){
		parent::__construct($config);
	}


	function display($cachable = false, $urlparams = false){
		//even in J2.5 JInput::set does not work in this case
    	if(version_compare(JVERSION,'3','lt')){
			$viewName=JRequest::getCmd('view',$this->default_view);
            if($viewName!='module' && $viewName!='products')$viewName=$this->default_view;
			JRequest::setVar('view',$viewName);
		}else{
			$input=JFactory::getApplication()->input;
			$viewName=$input->get('view',$this->default_view);
            if($viewName!='module' && $viewName!='products')$viewName=$this->default_view;
			$input->set('view',$viewName);
		}
		parent::display($cachable, $urlparams);
		return $this;
	}


	public function getModel($name = 'Products', $prefix = 'customfiltersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


}