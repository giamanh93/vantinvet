<?php
/**
 * @package		mod_form
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modelform');

class modelQlForm extends JModelForm
{

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   11.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		if (!isset($this->form_name))$this->form_name='form'.md5(rand(0,1000));
		$form = $this->loadForm($this->form_name, $this->str_xml, array('control' => 'jform', 'load_data' => false));
		if (empty($form)) return false;
		return $this->form=$form;
	}

	/**
	 * Method to check data via jForm->validate 
	 *
	 * @param	array	$data		An array of data (post data) to be validated
	 * @return	bool  			true on success, false on failure
	 * @since	1.6
	 */
	function check($data)
	{
		$form=$this->getForm();
		if (1==$form->validate($data)) return true;
        else 
        {
        	$this->formErrors=$form->getErrors();
        	return false;	
        }
        
	}
	/**
	 * Method to bind data back to form after failed validation
	 *
	 * @param	array	$data		An array of data (post data) to be filled in form
	 * @return	bool  			true on success, false on failure
	 * @since	1.6
	 */
    public function fillForm($data)
	{
		//echo "<pre>";print_r($data);
		$form=$this->getForm();
		if ($form->bind($data)) return $this->form=$form; else return false;
	}
}