<?php
/**
 *
 * The Customfilters controller file
 *
 * @package 	customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2014 breakdesigns.net. All rights reserved.
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
jimport('joomla.application.component.controlleradmin');

/**
 * main controller class
 * @package		customfilters
 * @author		Sakis Terz
 * @since		1.0
 */
class CustomfiltersControllerCustomfilters extends JControllerAdmin{



	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.0
	 */
	public function getModel($name = 'Customfilter', $prefix = 'CustomfiltersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * savefilters task
	 *
	 *
	 * @return	void
	 * @since	1.0
	 * @author	Sakis Terz
	 */
	public function savefilters()
	{
		$user	= JFactory::getUser();
		if ($user->authorise('core.edit', 'com_customfilters')){
			$adv_setting_types=array(5,6,8);//display types which have advanced settings
			$model=$this->getModel();
			$app=JFactory::getApplication();
			$jinput=$app->input;
			$type_ids=$jinput->get('type_id',array(),'array');
			$alias=$jinput->get('alias',array(),'array');
			$smart_search=$jinput->get('smart_search',array(),'array');
			$expanded=$jinput->get('expanded',array(),'array');
			$scrollbar_after=$jinput->get('scrollbar_after',array(),'array');
			$slider_min_value=$jinput->get('slider_min_value',array(),'array');
			$slider_max_value=$jinput->get('slider_max_value',array(),'array');
			$filter_category_ids=$jinput->get('filter_categories',array(),'array');

			$params_array=array();
			JArrayHelper::toInteger($type_ids);
			JArrayHelper::toInteger($smart_search);
			JArrayHelper::toInteger($expanded);
			JArrayHelper::toInteger($slider_min_value);
			JArrayHelper::toInteger($slider_max_value);
			JArrayHelper::toString($alias);
			JArrayHelper::toString($scrollbar_after);

			//store the params in an assoc array an use the item id as key
			foreach($smart_search as $key=>$val){
				$params_array[$key]=array('smart_search'=>$val,'expanded'=>$expanded[$key],'scrollbar_after'=>$scrollbar_after[$key]);
				if(in_array($type_ids[$key], $adv_setting_types)){
					if($type_ids[$key]=='6'){//slider
						$params_array[$key]['slider_min_value']=$slider_min_value[$key];
						$params_array[$key]['slider_max_value']=$slider_max_value[$key];
					}
					$params_array[$key]['filter_category_ids']=$filter_category_ids[$key];
				}
			}

			$params_formated=$this->formatParams($params_array);
			//sanitize the input to be int


			if($type_ids||$alias||$params_fromated){
				if(!$model->savefilters($type_ids,$alias,$params_formated)){
					JError::raiseWarning(500, $model->getError());
				}else $this->setMessage(JText::_('COM_CUSTOMFILTERS_FILTERS_SAVED_SUCCESS'));
			}
		}
		$this->setRedirect('index.php?option=com_customfilters&view=customfilters');
	}

	/**
	 *Create an array with the params as json string
	 *
	 * @return	array
	 * @since	1.5.3
	 * @author	Sakis Terz
	 */
	public function formatParams($params_array){
		$params_array_formated=array();

		foreach($params_array as $key=>$array){
			$reg=new JRegistry();
			$reg->loadArray($array);
			$params_array_formated[$key]=$reg->toString();
		}
		return $params_array_formated;
	}

}