<?php
/**
 *
 * Customfilters pagination class
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2008 - 2012 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: cfpagination.php 1 2012-12-22 16:23:00Z sakis $
 */

defined('_JEXEC') or die;
jimport('joomla.html.pagination');

/**
 * The class that extends the JPagination
 * Since VM does not allow to use the default JPagination in the layout - Should be extended
 *
 * @package customfilters
 * @author Sakis Terz
 */
class cfPagination extends JPagination{
	protected $menuparams;
	protected $_perRow;
	
	function __construct($total, $limitstart, $limit, $perRow=3){
		$this->prefix='com_customfilters';
		$app=JFactory::getApplication();
		$jinput=$app->input;
		$option=$jinput->get('option','','cmd');
		$current_itemId=$jinput->get('Itemid','0','int');		
		$this->menuparams=cftools::getMenuparams();	
		$this->cfinputs=CfInput::getInputs();		
		$this->_perRow = $this->menuparams->get('prod_per_row',3);
		
		
		parent::__construct($total, $limitstart, $limit);
		//ItemId
		if($option=='com_customfilters' && !empty($current_itemId))$itemId=$current_itemId; //valid also to the ajax requests
		
		if(!empty($itemId))$this->setAdditionalUrlParam('Itemid',$itemId);
		$vars=$this->getVarsArray();
		if(count($vars)>0){
			$vars['option']= 'com_customfilters';
		}
		foreach ($vars as $key=>$var){
			if(is_array($var)){
				for($i=0; $i<count($var); $i++){
					$var_name=$key."[$i]";
					if(isset($var[$i]))$this->setAdditionalUrlParam($var_name,$var[$i]);
				}
			}else $this->setAdditionalUrlParam($key,$var);
				
		}
		$this->setAdditionalUrlParam('tmpl',''); //reset the tmpl as it comes from the ajax requests
	}

	function getLimitBox(){

		$url=$this->getStatURI();
		$url=JRoute::_($url);

		$myURI=JURI::getInstance($url);
		//if(!empty($itemId))$myURI->setVar('Itemid', $itemId);
		if($myURI->getQuery())$wildcard='&';
		else $wildcard='?';
		$url.=$wildcard;

		$limits = array ();
		$pagination_seq=$this->menuparams->get('pagination_list_sequence','12,24,36,48,60,72');
		$pagination_seq_array=explode(',', $pagination_seq);
				
		
		//$url='index.php?virtuemart_category_id[0]=3&virtuemart_category_id[1]=4&view=products&limit=5&option=com_customfilters&limit=';
		//var_dump(JRoute::_($url.'limit=5'));
		// Make the option list.
		foreach ($pagination_seq_array as $seq) {
			$seq=(int)trim($seq);
			if($seq< $this->_perRow)continue; //it should be higher than the per row elements
			$limits[] = JHtml::_('select.option', 'limit='.$seq,$seq);
		}

		$js='onchange="window.top.location=\''.$url.'\'+this.options[this.selectedIndex].value"';
		$selected ='limit='.$this->limit;
		$html = JHtml::_('select.genericlist',  $limits,  'limit', 'class="inputbox" size="1"'.$js, 'value', 'text', $selected);

		return $html;

	}
	
	/**
	 * Creates the static part of the uri where the limit var will be added
	 *
	 * @package customfilters
	 * @since 1.0
	 * @author Sakis Terz
	 */
	function getStatURI(){
		$jinput=JFactory::getApplication()->input;
		$query_ar=$this->getVarsArray();
		//print_r($query_ar);
		if(count($query_ar)>0){
			$query_ar['option']= 'com_customfilters';
			$query_ar['view']= $jinput->getCmd('view','');
		}
		$u=JFactory::getURI();
		$query=$u->buildQuery($query_ar);
		$uri='index.php?'.$query;
		return $uri;
	}

	function getVarsArray(){
		$jinput=JFactory::getApplication()->input;
		$query_ar=array();
		$vars=$jinput->getArray($_REQUEST);

		foreach($vars as $key=>$val){
			$is_custom_filter=strpos($key,'custom_f_');
			if($key=='virtuemart_category_id' || $key=='virtuemart_manufacturer_id' || $is_custom_filter!==false || $key=='price_from' || $key=='price_to' || $key=='Itemid' || $key=='q'){
				//basic filtering for the variables except custom fields
				if(is_array($val)){
					if(count($val)==1 && $is_custom_filter===false && isset($val[0]))$val[0]=(int)$val[0];
					$val=array_filter($val);
				}

				if(!empty($val)){
					$query_ar[$key]=$val;
				}
			}
		}
		//print_r($query_ar);
		return $query_ar;
	}
}