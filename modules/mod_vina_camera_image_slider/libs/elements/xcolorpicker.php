<?php
/**
 * @package X Color Picker for Joomla! 2.5
 * @author http://Thecoders.vn
 * @copyright (C) 2011- Thecoders.vn
 * @license http://www.gnu.org/licenseses/gpl-2.0.html GNU/GPL
**/

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');

class JFormFieldXColorpicker extends JFormField
{
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$type = 'xcolorpicker';
	
	function getInput()
	{
		$uri = $this->getCurrentURL();
		$this->loadjscss($uri);
		$value = $this->value ? $this->value : (string)$this->element['default'];
		$string =  '<input class="color" type="text" value="'.$value.'" name="'.$this->name.'" >';
		return $string;	
	}
	
	/**
	 * get current url
	 */
	function getCurrentURL()
	{
		$uri = str_replace(DS, "/", str_replace(JPATH_SITE, JURI::base(), dirname(__FILE__)));
		$uri = str_replace("/administrator", "", $uri);
		return $uri;
	}
	
	/**
	 * load css and js file
	 */
	function loadjscss($uri)
	{
		if(!defined('TCVN_PARAM_HELPER_RAINBOW_'))
		{
			define('TCVN_PARAM_HELPER_RAINBOW_', 1);
			JHTML::script($uri . "/" . 'xcolorpicker/jscolor.js');
		}	
	} 
}
?>