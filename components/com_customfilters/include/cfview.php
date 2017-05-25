<?php
/**
 *
 * Customfilters view
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2008-2012 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: cfview.php 1 2011-12-23 20:54:00Z sakis $
 */

//defined
defined('_JEXEC') or die;

//import the view class
jimport('joomla.application.component.view');



/**
 * Extends the JView
 * @author	Sakis Terz
 * @since	1.0
 */
class cfView extends JViewLegacy{

	/**
	 * Execute and display a template script.
	 *
	 * @param string The name of the template file to parse;
	 * automatically searches through the template paths.
	 *
	 * @throws object An JError object.
	 * @see fetch()
	 */
	function display($tpl = null)
	{	
		$result = $this->loadTemplate($tpl);
		if (JError::isError($result)) {
			return $result;
		}
		echo $result;
	}

	/**
	 * Function that loads the template in the view
	 * @since	1.0
	 * @param string The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 * @see JView::loadTemplate()
	 */
	function loadTemplate($tpl = null)
	{
		// clear prior output
		$this->_output = null;

		$template = JFactory::getApplication()->getTemplate();
		$layout = $this->getLayout();
		$layoutTemplate = $this->getLayoutTemplate();

		//create the template file name based on the layout
		$file = isset($tpl) ? $layout.'_'.$tpl : $layout;
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = isset($tpl)? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the language file for the template
		$lang	= JFactory::getLanguage();
		$lang->load('tpl_'.$template, JPATH_BASE, null, false, false)
		||	$lang->load('tpl_'.$template, JPATH_THEMES."/$template", null, false, false)
		||	$lang->load('tpl_'.$template, JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load('tpl_'.$template, JPATH_THEMES."/$template", $lang->getDefault(), false, false);

		// change the template folder if alternative layout is in different template
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		}

		//set the VM vars to the template path
		$viewName=$this->getName();
		$this->_path['template'] = str_replace('com_customfilters', 'com_virtuemart', $this->_path['template']);
		$this->_path['template'] = str_replace($viewName, 'category', $this->_path['template']);

		// load the template script
		jimport('joomla.filesystem.path');
		$filetofind	= $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		// If alternate layout can't be found, fall back to default layout
		if ($this->_template == false)
		{
			$filetofind = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		if ($this->_template != false)
		{
			// unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// never allow a 'this' property
			if (isset($this->this)) {
				unset($this->this);
			}

			// start capturing output into a buffer
			ob_start();
			// include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else {
			return JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file));
		}
	}

}