<?php
/**
 * @package It Add Row for Joomla! 2.5
 * @author http://Thecoders.vn
 * @copyright (C) 2011- Thecoders.vn
 * @license http://www.gnu.org/licenseses/gpl-2.0.html GNU/GPL
**/
 
// no direct access
defined('_JEXEC') or die ('Restricted access');
  
  class JFormFieldItaddrow extends JFormField {
  	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'ItAddRow';
	
	function getInput()
	{
		
		$values = $this->value; 
		$name = $this->name;

		$cname =''.$name.'[]';
		$id = str_replace("jform_params_","",$this->id);
		if( !is_array($values) && empty($values) ){
			$values = array();
		}
		$values = !is_array($values) ? array($values):$values;
		$row ='';
		foreach( $values as $key=> $value ){
			$row .= '
				<div class="row">
					<span class="spantext">'.($key+1).'</span>
					<input type="text" value="'.$value.'" name="'.$cname.'">
					<span class="remove"></span>
				</div>
			';
		}
		return '<fieldset class="it-addrow-block"><div><span id="btna-'.$id.'" class="add">Add Row</span></div>'.$row.'</fieldset>';
	}
  }
?>