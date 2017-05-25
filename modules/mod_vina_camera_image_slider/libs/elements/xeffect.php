<?php
/**
 * @package X Effect for Joomla! 2.5
 * @author http://Thecoders.vn
 * @copyright (C) 2011- Thecoders.vn
 * @license http://www.gnu.org/licenseses/gpl-2.0.html GNU/GPL
**/

// no direct access
defined('_JEXEC') or die ('Restricted access');
class JFormFieldXEffect extends JFormField
{

    /**
     * @access private
     */
	var	$_name 		= 'xeffect';
	var $_params 	= null;
	
	function __getValue($name1, $name2, $default='')
	{
		$val = $this->form->getValue($name1, 'params', $default);
		return is_array($val)&& isset($val[$name2])? $val[$name2]:$default;
	}
	
	function getInput()
	{
		$value = $this->value; 
		$name  = $this->element->getAttribute("name");
		
		$control_name ='jform[params]';

		$elms = array("preview" => array("text"=>JText::_("Path Main Preview"))   ,
					  "path"   => array("text"=>JText::_("Path Thumbnail Image"))  ,
					  "title"   => array("text"=>JText::_("File Title"))   ,
					  "link"  => array("text"=>JText::_("Link"))); 
		
		$ynoptions = array('0'  => JText::_('No'),
							'1' =>  JText::_('Yes')
						 );
			
		$string = '<div style="clear:both" ><label  style="clear:both"><b>'.JText::_("Is Enabled").'</b></label> <select class="tcvn-showcontainer" id="tcvn-group'.$name.'" name="'.$control_name.'['.$name.'][enable]">';
					foreach($ynoptions as $key => $option){
						$selected = $key == $this->__getValue($name,'enable','')?' selected="selected" ':'';
						
						$string .= '<option value="'.$key.'" '.$selected .'>'.$option.'</option>';
					}
		$string .='</select></div>';
		
		
		$string .='<div class="tcvn-container"  id="tcvn-group'.$name.'container"><fieldset class="tcvn-fsi"><legend>'.JText::_('File Setting').'</legend>';
		$key = '';
		
		$ftoptions = array('image' =>  JText::_('Image'));
		$string .= '<div class="tcvn-cols" ><label>'.JText::_("File Type").'</label><select id="" name="'.$control_name.'['.$name.'][filetype]">';
					foreach($ftoptions as $key => $option){
						$selected = $key == $this->__getValue($name,'filetype','')?' selected="selected" ':'';
						
						$string .='<option value="'.$key.'" '.$selected .'>'.$option.'</option>';
					}
		$string .='</select></div>';
		
		$tgs = array("_parent" => JText::_("Open In Parent"), 
					 "_self"  => JText::_("Open Self"),
					  "_blank" => JText::_("Open New Window"));
		$string .= '<div class="tcvn-cols" ><label>'.JText::_("Target Open Link").'</label><select id="" name="'.$control_name.'['.$name.'][target_open]">';
					foreach($tgs as $key => $option){
						$selected = $key == $this->__getValue($name,'target_open','') ?' selected="selected" ':'';
						
						$string .='<option value="'.$key.'" '.$selected .'>'.$option.'</option>';
					}
		$string .='</select></div>';
		

		foreach($elms as $key=>$elm){
			$string .= '<div class="tcvn-cols" ><label>'.$elm['text'].'</label><input type="text" value="'. $this->__getValue($name,$key,'')  .'" id="jform_params_'.$name.'_'.$key.'" name="'.
						$control_name.'['.$name.']['.$key.']'
					.'"/></div>';
		
		
		}
		$string .='<textarea name="'.$control_name.'['.$name.'][content]" style="width:95%;min-height:60px">'. $this->__getValue($name,'content','').'</textarea></fieldset>';
			
		return $string;
		
		$string .= '<fieldset class="tcvn-fsi"><legend>'.JText::_('Effect Slider').'</legend>';
	
		$positions = array(''=>JText::_("Automatic"),
							  'left'=>JText::_("Left"),
							  'right'=>JText::_("Right"))
							;
		$string .= '';
	
		$elms = array("time"   => array("text"=>JText::_("Time"),'default'=>'12')); 
		
		foreach($elms as $key=>$elm){
			$value = ($tmp= $this->__getValue($name,$key,''))? $tmp : $elm['default'];
			
			$string .= '<div class="tcvn-cols" ><label>'.$elm['text'].'</label><input type="text" value="'.$value.'" id="" name="'.
						$control_name.'['.$name.']['.$key.']'
					.'"/></div>';
		}
		$string .='<div class="tcvn-cols" ><label>'.JText::_('Image Position').'</label><select name="'.$control_name.'['.$name.'][imagepos]'.'" id="">';
		foreach($positions as $key => $tran){
			$selected = $key ==  $this->__getValue($name,'imagepos','')?' selected="selected" ':'';
			$string .= '<option '.$selected.' value="'.$key.'">'.$tran.'</option>';
		}
		
		$string .= '</select></div>'; return $string;
		$string .='<div class="tcvn-cols" ><label>'.JText::_('Is Panned Effect').'</label><select name="'.$control_name.'['.$name.'][ispanned]'.'" id="">';
		foreach($ynoptions as $key => $tran){
			$selected = $key ==  $this->__getValue($name,'ispanned','') ?' selected="selected" ':'';
			$string .= '<option '.$selected.' value="'.$key.'">'.$tran.'</option>';
		}
		$string .= '</select></div></fieldset>';
		$tmp = '';
		return $string.'</div>';
	}
	
}
?>