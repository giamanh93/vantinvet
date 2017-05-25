<?php
/**
 *
 * The basic controller file
 *
 * @package 	customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: controller.php 1 2011-10-21 18:36:00Z sakis $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * main controller class
 * @package		customfilters
 * @author		Sakis Terz
 * @since		1.0
 */
class CustomfiltersController extends JControllerLegacy{
	
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{	
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','customfilters','');
		if($view=='customfilters' || $view==''){
			//$this->_createFilters();
			$model=$this->getModel();
			$model->refreshUpdateSite();
		}		
		parent::display();
		return $this;
	}
	
	/**
	 * 
	 * Function to load the existing custom fields to the filters table
	 * @author	Sakis Terz
	 * @since	1.0
	 * @return	void
	 */
	protected function _createFilters(){
		$model=$this->getModel();
		if(!$model->createFilters()) JError::raiseWarning(500,$model->getError());
	}
	
	/**
	 * Function to get version info
	 */
	public function getVersionInfo(){
		$model=$this->getModel('customfilters');
		$html_result=$model->getVersionInfo();
		if($html_result)echo json_encode($html_result);
		else echo '';
		jexit();
	}
}