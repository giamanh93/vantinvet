<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved by the author.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id:$
 */

// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

if (!class_exists ('VmModel')) {
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
}

/**
 * Model for VirtueMart Customs Fields
 *
 * @package        VirtueMart
 */
class VirtueMartModelCustomfields extends VmModel {

	/** @var array For roundable values */
	static $dimensions = array('product_length','product_width','product_height','product_weight');
	static $useAbsUrls = false;
	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 *
	 * @author Max Milbers
	 */
	function __construct () {

		parent::__construct ('virtuemart_customfield_id');
		$this->setMainTable ('product_customfields');
	}

	/**
	 * Gets a single custom by virtuemart_customfield_id
	 *
	 * @param string $type
	 * @param string $mime mime type of custom, use for exampel image
	 * @return customobject
	 */
	function getCustomfield ($id = 0) {

		return $this->getData($id);

	}

	public static function getProductCustomSelectFieldList(){

		$q = 'SELECT c.`virtuemart_custom_id`, c.`custom_parent_id`, c.`virtuemart_vendor_id`, c.`custom_jplugin_id`, c.`custom_element`, c.`admin_only`, c.`custom_title`, c.`show_title` , c.`custom_tip`,
		c.`custom_value`, c.`custom_desc`, c.`field_type`, c.`is_list`, c.`is_hidden`, c.`is_cart_attribute`, c.`is_input`, c.`layout_pos`, c.`custom_params`, c.`shared`, c.`published`, c.`ordering`, ';
		$q .= 'field.`virtuemart_customfield_id`, field.`virtuemart_product_id`, field.`customfield_value`, field.`customfield_price`,
		field.`customfield_params`, field.`published` as fpublished, field.`override`, field.`disabler`, field.`ordering`
		FROM `#__virtuemart_customs` AS c LEFT JOIN `#__virtuemart_product_customfields` AS field ON c.`virtuemart_custom_id` = field.`virtuemart_custom_id` ';
		return $q;
	}


	public static function getCustomEmbeddedProductCustomField($virtuemart_customfield_id){

		static $_customFieldById = array();

		if(!isset($_customFieldById[$virtuemart_customfield_id])){
			$db= JFactory::getDBO ();
			$q = VirtueMartModelCustomfields::getProductCustomSelectFieldList();
			if($virtuemart_customfield_id){
				$q .= ' WHERE `virtuemart_customfield_id` ="' . (int)$virtuemart_customfield_id . '"';
			}
			$db->setQuery ($q);
			$_customFieldById[$virtuemart_customfield_id] = $db->loadObject ();
			if($_customFieldById[$virtuemart_customfield_id]){
				VirtueMartModelCustomfields::bindCustomEmbeddedFieldParams($_customFieldById[$virtuemart_customfield_id],$_customFieldById[$virtuemart_customfield_id]->field_type);
			}
		}

		return $_customFieldById[$virtuemart_customfield_id];
	}

	function getCustomEmbeddedProductCustomFields($productIds,$virtuemart_custom_id=0,$cartattribute=-1,$forcefront=FALSE){

		$app = JFactory::getApplication();
		$db= JFactory::getDBO ();
		$q = VirtueMartModelCustomfields::getProductCustomSelectFieldList();

		static $_customFieldByProductId = array();

		$hashCwAttribute = $cartattribute;
		if($hashCwAttribute==-1) $hashCwAttribute = 2;
		$productCustomsCached = array();
		foreach($productIds as $k=>$productId){
			$hkey = (int)$productId.$hashCwAttribute;
			if (array_key_exists ($hkey, $_customFieldByProductId)) {
				//$productCustomsCached = $_customFieldByProductId[$hkey];
				$productCustomsCached = array_merge($productCustomsCached,$_customFieldByProductId[$hkey]);
				unset($productIds[$k]);
			}
		}

		if(is_array($productIds) and count($productIds)>0){
			$q .= 'WHERE `virtuemart_product_id` IN ('.implode(',', $productIds).')';
		} else if(!empty($productIds)){
			$q .= 'WHERE `virtuemart_product_id` = "'.$productIds.'" ';
		} else {
			return $productCustomsCached;
		}
		if(!empty($virtuemart_custom_id)){
			if(is_numeric($virtuemart_custom_id)){
				$q .= ' AND c.`virtuemart_custom_id`= "' . (int)$virtuemart_custom_id.'" ';
			} else {
				$virtuemart_custom_id = substr($virtuemart_custom_id,0,1); //just in case
				$q .= ' AND c.`field_type`= "' .$virtuemart_custom_id.'" ';
			}
		}
		if(!empty($cartattribute) and $cartattribute!=-1){
			$q .= ' AND ( `is_cart_attribute` = 1 OR `is_input` = 1) ';
		}
		if($forcefront or $app->isSite()){
			$q .= ' AND c.`published` = "1" ';
			$forcefront = true;
		}

		if(!empty($virtuemart_custom_id) and $virtuemart_custom_id!==0){
			$q .= ' ORDER BY field.`ordering` ASC';
		} else {
			if($forcefront or $app->isSite()){
				//$q .= ' GROUP BY c.`virtuemart_custom_id`';
			}

			$q .= ' ORDER BY field.`ordering`,`virtuemart_custom_id` ASC';
		}

		$db->setQuery ($q);
		$productCustoms = $db->loadObjectList ();
		$err=$db->getErrorMsg();
		if($err){
			vmError('getCustomEmbeddedProductCustomFields error in query '.$err);
		}

		foreach($productCustoms as $customfield){
			$hkey = (int)$customfield->virtuemart_product_id.$hashCwAttribute;
			$_customFieldByProductId[$hkey][] = $customfield;
		}

		$productCustoms = array_merge($productCustomsCached,$productCustoms);
		if($productCustoms){

			$customfield_ids = array();
			$customfield_override_ids = array();
			foreach($productCustoms as $field){

				if($field->override!=0){
					$customfield_override_ids[] = $field->override;
				} else if ($field->disabler!=0) {
					$customfield_override_ids[] = $field->disabler;
				}

				$customfield_ids[] = $field->virtuemart_customfield_id;
			}
			$virtuemart_customfield_ids = array_unique( array_diff($customfield_ids,$customfield_override_ids));

			foreach ($productCustoms as $k =>$field) {
				if(in_array($field->virtuemart_customfield_id,$virtuemart_customfield_ids)){

					if($forcefront and $field->disabler){
						unset($productCustoms[$k]);
					} else {
						VirtueMartModelCustomfields::bindCustomEmbeddedFieldParams($field,$field->field_type);
					}

				} else{
					unset($productCustoms[$k]);
				}
			}
			return $productCustoms;
		} else {
			return array();
		}
	}


	static function bindCustomEmbeddedFieldParams(&$obj,$fieldtype){

		//vmdebug('bindCustomEmbeddedFieldParams begin',$obj);
		if(!class_exists('VirtueMartModelCustom')) require(VMPATH_ADMIN.DS.'models'.DS.'custom.php');

		if ($obj->field_type == 'E') {
			JPluginHelper::importPlugin ('vmcustom');
			$dispatcher = JDispatcher::getInstance ();
			$retValue = $dispatcher->trigger ('plgVmDeclarePluginParamsCustomVM3', array(&$obj));
			if(!empty($obj->_varsToPushParam)){
				if(empty($obj->_varsToPushParamCustom)) $obj->_varsToPushParamCustom = $obj->_varsToPushParam;
				if(empty($obj->_varsToPushParamCustomField)) $obj->_varsToPushParamCustomField = $obj->_varsToPushParam;
			}
		} else {
			$obj->_varsToPushParamCustom = VirtueMartModelCustom::getVarsToPush($fieldtype);
			$obj->_varsToPushParam = $obj->_varsToPushParamCustomField = $obj->_varsToPushParamCustom;
			//vmdebug('my $obj->_varsToPushParamCustom',$obj->_varsToPushParamCustomField);
		}

		if(!empty($obj->_varsToPushParam)){
			//$obj ->_xParams = 'custom_params';
			VmTable::bindParameterable($obj,'custom_params',$obj->_varsToPushParamCustom);

			$obj ->_xParams = 'customfield_params';
			VmTable::bindParameterable($obj,$obj->_xParams,$obj->_varsToPushParamCustomField);
		}

	}


	private function sortChildIds ($product_id, $childIds, $sorted=array()){

		//vmdebug('sortChildIds',$product_id, $childIds);
		foreach($childIds as $childIdKey => $childs){
			if(!is_array($childs)){
				$sorted[] = array('parent_id'=>$product_id,'vm_product_id'=>$childs);
				if(isset($childIds[$childs]) and is_array($childIds[$childs])){
					$sorted = self::sortChildIds($childs, $childIds[$childs], $sorted);
					//unset($childIds[$childs]);
				}
			} else {
				//$sorted = self::sortChildIds($childIdKey, $childs, $sorted);
			}
		}
		return $sorted;
	}


	private function renderProductChildLine($i,$line,$field,$productModel,$row,$showSku){

		$child = $productModel->getProductSingle($line['vm_product_id'],false);
		$readonly = '';
		$classBox = 'class="inputbox"';
		if($line['parent_id'] == $line['vm_product_id']){
			$readonly = 'readonly="readonly"';
			$classBox = 'class="readonly"';
		}
		$linkLabel = $line['parent_id'] .'->'. $line['vm_product_id'].' ';
		$html = '<tr class="row'.(($i+1)%2).'">';
		$html .= '<td>'.JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$child->virtuemart_product_id), $linkLabel.$child->slug, array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '.$child->slug)).'</td>';
		if($showSku) $html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][product_sku]" id="child'.$child->virtuemart_product_id.'product_sku" size="20" maxlength="64" value="'.$child->product_sku .'" /></td>';
		$html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][product_gtin]" id="child'.$child->virtuemart_product_id.'product_gtin" size="13" maxlength="13" value="'.$child->product_gtin .'" /></td>';
		/*$html .= 	'<input type="hidden" name="childs['.$child->virtuemart_product_id .'][product_name]" id="child'.$child->virtuemart_product_id .'product_name" value="'.$child->product_name .'" />
					<input type="hidden" name="childs['.$child->virtuemart_product_id .'][slug]" id="child'.$child->virtuemart_product_id .'slug" value="'.$child->slug .'" />
					<input type="hidden" name="childs['.$child->virtuemart_product_id .'][product_parent_id]" id="child'.$child->virtuemart_product_id .'parent" value="'.$child->product_parent_id .'" />';
		*/
		//$html .= 	$child->product_name .'</td>';
		//$html .=	'<td>'.$child->allPrices[$child->selectedPrice]['product_price'] .'</td>';
		$html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][mprices][product_price][]" size="8" value="'. $child->allPrices[$child->selectedPrice]['product_price'] .'" />
		<input type="hidden" name="childs['. $child->virtuemart_product_id .'][mprices][virtuemart_product_price_id][]" value="'. $child->allPrices[$child->selectedPrice]['virtuemart_product_price_id'] .'"  ></td>';

		//We dont want to update always the stock, this would lead to wrong stocks, if the store has activity, while the vendor is editing the product
		//$html .= '<td><input '.$readonly.' '.$class.' type="text" name="childs['.$child->virtuemart_product_id.'][product_in_stock]" id="child'.$child->virtuemart_product_id.'product_in_stock" size="3" maxlength="6" value="'.$child->product_in_stock .'" /></td>';
		//$html .= '<td><input '.$readonly.' '.$class.' type="text" name="childs['.$child->virtuemart_product_id.'][product_in_stock]" id="child'.$child->virtuemart_product_id.'product_in_stock" size="3" maxlength="6" value="'.$child->product_in_stock .'" /></td>';
		$html .= '<td>'.$child->product_in_stock .'</td>';
		$html .= '<td>'.$child->product_ordered.'</td>';

		$product_id = $line['vm_product_id'];
		if(empty($field->selectoptions)) $field->selectoptions = array();
		foreach($field->selectoptions as $k=>$selectoption){
			//vmdebug('my $field->options',$field->options);
			//if(!isset($field->options)) continue;

			$class ='';
			if($selectoption->voption=='clabels'){
				$name = 'field[' . $row . '][options]['.$product_id.']['.$k.']';
				$myoption = false;
				if(isset($field->options->$product_id)){
					$myoption = $field->options->$product_id;
				}

				if(!isset($myoption[$k])){
					$value = 0;
				} else {
					$value = trim($myoption[$k]);
				}
				$idTag = 'cvarl.'.$product_id.'s'.$k;
			} else {
				$name = 'childs['.$product_id .']['.$selectoption->voption.']';
				$value = trim($child->{$selectoption->voption});
				$idTag = 'cvard.'.$product_id.'s'.$k;
				$class = array('class'=>'cvard');
			}

			if(count($selectoption->comboptions)>0){
				$html .= '<td>'.JHtml::_ ('select.genericlist', $selectoption->comboptions,$name , $class, 'value', 'text',
				$value ,$idTag);
				if($selectoption->voption!='clabels'){
					$html .= '<input type="hidden" name="field[' . $row . '][options]['.$product_id.']['.$k.']" value="'.$value .'" />';
				}
				$html .= '</td>';
			}
		}
		$html .= '</tr>';
		return $html;
	}

	/**
	 * @author Max Milbers
	 * @param $field
	 * @param $product_id
	 * @param $row
	 */
	public function displayProductCustomfieldBE ($field, $product, $row) {

		//This is a kind of fallback, setting default of custom if there is no value of the productcustom
		$field->customfield_value = empty($field->customfield_value) ? $field->custom_value : $field->customfield_value;
		$field->customfield_price = empty($field->customfield_price) ? 0 : $field->customfield_price;

		if(is_object($product)){
			$product_id = $product->virtuemart_product_id;
			$virtuemart_vendor_id = $product->virtuemart_vendor_id;
		} else {

			$product_id = $product;
			$virtuemart_vendor_id = VmConfig::isSuperVendor();
			vmdebug('displayProductCustomfieldBE product was not object, use for productId '.$product_id.' and $virtuemart_vendor_id = '.$virtuemart_vendor_id);
		}
		//vmdebug('displayProductCustomfieldBE',$product_id,$field,$virtuemart_vendor_id,$product);
		//the option "is_cart_attribute" gives the possibility to set a price, there is no sense to set a price,
		//if the custom is not stored in the order.
		if ($field->is_input) {
			if(!class_exists('VirtueMartModelVendor')) require(VMPATH_ADMIN.DS.'models'.DS.'vendor.php');
			if(!class_exists('VirtueMartModelCurrency')) require(VMPATH_ADMIN.DS.'models'.DS.'currency.php');
			$vendor_model = VmModel::getModel('vendor');
			//$virtuemart_vendor_id = 1;
			$vendor = $vendor_model->getVendor($virtuemart_vendor_id);
			$currency_model = VmModel::getModel('currency');
			$vendor_currency = $currency_model->getCurrency($vendor->vendor_currency);

			$priceInput = '<span style="white-space: nowrap;"><input type="text" size="12" style="text-align:right;" value="' . $field->customfield_price . '" name="field[' . $row . '][customfield_price]" /> '.$vendor_currency->currency_symbol."</span>";
		}
		else {
			$priceInput = ' ';
		}

		switch ($field->field_type) {

			case 'C':
				//vmdebug('displayProductCustomfieldBE $field',$field);
				//if(!isset($field->withParent)) $field->withParent = 0;
				//if(!isset($field->parentOrderable)) $field->parentOrderable = 0;
				//vmdebug('displayProductCustomfieldBE',$field,$product);

				if(!empty($product->product_parent_id) and $product->product_parent_id==$field->virtuemart_product_id){
					return 'controlled by parent';
				}

				$html = '';
				if (!class_exists('VmHTML')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
				//$html = vmText::_('COM_VIRTUEMART_CUSTOM_WP').VmHTML::checkbox('field[' . $row . '][withParent]',$field->withParent,1,0,'');
				//$html .= vmText::_('COM_VIRTUEMART_CUSTOM_PO').VmHTML::checkbox('field[' . $row . '][parentOrderable]',$field->parentOrderable,1,0,'').'<br />';

				if(empty($field->selectoptions) or count($field->selectoptions)==0){
					$selectOption = new stdClass();	//The json conversts it anyway in an object, so suitable to use an object here
					$selectOption->voption = 'product_name';
					$selectOption->slabel = '';
					$selectOption->clabel = '';
					$selectOption->canonical = 0;
					$selectOption->values = '';
					$c = 0;
					$field->selectoptions = new stdClass();
					$field->selectoptions->$c = $selectOption;
					$field->options = new stdClass();

				} else if(is_array($field->selectoptions)){
					$field->selectoptions = (object)$field->selectoptions;
				}
				$field->options = (object)$field->options;

				$optAttr = array();

				$optAttr[] = array('value' => '0' ,'text' =>vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
				$optAttr[] = array('value' => 'product_name' ,'text' =>vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME'));
				$optAttr[] = array('value' => 'product_sku', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_SKU'));
				$optAttr[] = array('value' => 'slug', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_ALIAS'));
				$optAttr[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
				$optAttr[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
				$optAttr[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
				$optAttr[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));
				$optAttr[] = array('value' => 'clabels', 'text' => vmText::_ ('COM_VIRTUEMART_CLABELS'));


				$productModel = VmModel::getModel('product');

				$childIds = array();
				$sorted = array();
				//vmSetStartTime();
				$productModel->getAllProductChildIds($product_id,$childIds);
				if(isset($childIds[$product_id])){
					$sorted = self::sortChildIds($product_id,$childIds[$product_id]);
				}
				array_unshift($sorted,  array('parent_id' => $product_id, 'vm_product_id' => $product_id));

				$showSku = true;

				$k = 0;
				if(empty($field->selectoptions)) $field->selectoptions = array();
				foreach($field->selectoptions as $k=>&$soption){
					$options = array();
					$options[] = array('value' => '0' ,'text' =>vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));

					$added = array();

					if($soption->voption!='clabels'){

						foreach($sorted as $vmProductId){
							if(empty($vmProductId) or $vmProductId['vm_product_id']==$product_id){
								continue;
							}
							$product = $productModel->getProductSingle($vmProductId['vm_product_id'],false);
							$voption = trim($product->{$soption->voption});

							if(!empty($voption)) {
								$found = false;
								//Guys, dont tell me about in_array or array_search, it does not work here
								foreach($added as $add){
									if($add == $voption){
										$found = true;
									}
								}
								if(!$found){
									$added[] = $voption;
								}
							}
						}

						if($soption->voption=='product_sku'){
							$showSku = false;
						}
					}

					if(!empty($soption->values)){
						$values = explode("\n",$soption->values);
						foreach($values as $value){
							$found = false;
							$value = trim($value);
							foreach($added as $add){
								if($add == $value){
									$found = true;
									vmdebug('Found true due $soption->values');
								}
							}
							if(!$found){
								$added[] = $value;
							}
						}
					}

					$soption->values = implode("\n",$added);

					foreach($added as &$value){
						$options[] = array('value' => $value ,'text' =>$value);
					}

					$soption->comboptions = $options;
					if(!isset($soption->clabel)) $soption->clabel = '';
					$soption->slabel = empty($soption->clabel)? vmText::_('COM_VIRTUEMART_'.strtoupper($soption->voption)): vmText::_($soption->clabel);

					if($k==0){
						$html .='<div style="float:left">';
					} else {
						$html .='<div class="removable">';
					}

					$idTag = 'selectoptions'.$k;
					$html .= JHtml::_ ('select.genericlist', $optAttr, 'field[' . $row . '][selectoptions]['.$k.'][voption]', '', 'value', 'text', $soption->voption,$idTag) ;
					$html .= '<input type="text" value="' . $soption->clabel . '" name="field[' . $row . '][selectoptions]['.$k.'][clabel]" style="line-height:2em;margin:5px 5px 0;" />';
					$html .= '<textarea name="field[' . $row . '][selectoptions]['.$k.'][values]" rows="5" cols="35" style="float:none;margin:5px 5px 0;" >'.$soption->values.'</textarea>';

					if($k>0){
						$html .='<span class="vmicon vmicon-16-remove"></span>';
					} else {

					}
					$html .='</div>';
					if($k==0){
						$html .= '<div style="float:right;max-width:60%;width:45%;min-width:30%" >'.vmText::_('COM_VIRTUEMART_CUSTOM_CV_DESC').'</div>';
						$html .= '<div class="clear"></div>';
					}
				}

				$idTag = 'selectoptions'.++$k;
				$html .= '<fieldset style="background-color:#F9F9F9;">
					<legend>'. vmText::_('COM_VIRTUEMART_CUSTOM_RAMB_NEW').'</legend>
					<div id="new_ramification">';
				//$html .= JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][selectoptions]['.$k.'][voption]', '', 'value', 'text', 'product_name',$idTag) ;
				//$html .= '<input type="text" value="" name="field[' . $row . '][selectoptions]['.$k.'][slabel]" />';

				$html .= JHtml::_ ('select.genericlist', $optAttr, 'voption', '', 'value', 'text', 'product_name','voption') ;
				$html .= '<input type="text" value="" id="vlabel" name="vlabel" />';

				$html .= '<span id="new_ramification_bt"><span class="icon-nofloat vmicon vmicon-16-new"></span>'. vmText::_('COM_VIRTUEMART_ADD').'</span>
					</div>
				</fieldset>';

				vmJsApi::addJScript('new_ramification',"
	jQuery( function($) {
		$('#new_ramification_bt').click(function() {
			var Prod = $('.new_ramification');//obsolete?

			var voption = jQuery('#voption').val();
			var label = jQuery('#vlabel').val();
				//console.log ('my label '+label);
			form = document.getElementById('adminForm');
			var newdiv = document.createElement('div');
			newdiv.innerHTML = '<input type=\"text\" value=\"'+voption+'\" name=\"field[" . $row . "][selectoptions][".$k."][voption]\" /><input type=\"text\" value=\"'+label+'\" name=\"field[" . $row . "][selectoptions][".$k."][clabel]\" />';
			form.appendChild(newdiv);

			form.task.value = 'apply';
			form.submit();
			return false;
		});
	});
	");

				if ($product_id) {
					$link=JRoute::_('index.php?option=com_virtuemart&view=product&task=createChild&virtuemart_product_id='.$product_id.'&'.JSession::getFormToken().'=1&target=parent' );
					$add_child_button="";
				} else {
					$link="";
					$add_child_button=" not-active";
				}

				$html .= '<div class="button2-left '.$add_child_button.' btn-wrapper">
						<div class="blank">';
				if ($link) {
					$html .= '<a href="'. $link .'" class="btn btn-small">';
				} else {
					$html .= '<span class="hasTip" title="'.vmText::_ ('COM_VIRTUEMART_PRODUCT_ADD_CHILD_TIP').'">';
				}
				$html .= vmText::_('COM_VIRTUEMART_PRODUCT_ADD_CHILD');
				if ($link) {
					$html .= '</a>';
				} else{
					$html .= '</span>';
				}
				$html .= '</div>
					</div><div class="clear"></div>';
				//vmdebug('my $field->selectoptions',$field->selectoptions,$field->options);
				$html .= '<table id="syncro">';
				$html .= '<tr>
<th style="text-align: left !important;width:130px;">#</th>';
				if($showSku){
					$html .= '<th style="text-align: left !important;width:90px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_SKU').'</th>';
				}
				$html .= '<th style="text-align: left !important;width:80px;">'. vmText::_('COM_VIRTUEMART_PRODUCT_GTIN').'</th>
<th style="text-align: left !important;" width="5%">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST').'</th>
<th style="text-align: left !important;width:30px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK').'</th>
<th style="text-align: left !important;width:30px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK').'</th>';
				foreach($field->selectoptions as $k=>$option){
					$html .= '<th>'.vmText::_('COM_VIRTUEMART_'.strtoupper($option->voption)).'</th>';
				}
				$html .= '</tr>';



				if(isset($childIds[$product_id])){
					foreach($sorted as $i=>$line){
						$html .= self::renderProductChildLine($i,$line,$field,$productModel,$row,$showSku);
					}
				}


				$html .= '</table>';

				return $html;
				// 					return 'Automatic Childvariant creation (later you can choose here attributes to show, now product name) </td><td>';
				break;
			case 'A':
				//vmdebug('displayProductCustomfieldBE $field',$field);
				if(!isset($field->withParent)) $field->withParent = 0;
				if(!isset($field->parentOrderable)) $field->parentOrderable = 0;
				//vmdebug('displayProductCustomfieldBE',$field);
				if (!class_exists('VmHTML')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
				$html = '</td><td>' . vmText::_('COM_VIRTUEMART_CUSTOM_WP').VmHTML::checkbox('field[' . $row . '][withParent]',$field->withParent,1,0,'').'<br />';
				$html .= vmText::_('COM_VIRTUEMART_CUSTOM_PO').VmHTML::checkbox('field[' . $row . '][parentOrderable]',$field->parentOrderable,1,0,'');

				$options = array();
				$options[] = array('value' => 'product_name' ,'text' =>vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME'));
				$options[] = array('value' => 'product_sku', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_SKU'));
				$options[] = array('value' => 'slug', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_ALIAS'));
				$options[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
				$options[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
				$options[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
				$options[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));

				$html .= JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', '', 'value', 'text', $field->customfield_value) ;
				return $html;
				// 					return 'Automatic Childvariant creation (later you can choose here attributes to show, now product name) </td><td>';
				break;
			/* string or integer */
			case 'B':
			case 'S':

				if($field->is_list){
					$options = array();
					$values = explode (';', $field->custom_value);

					foreach ($values as $key => $val) {
						$options[] = array('value' => $val, 'text' => $val);
					}

					$currentValue = $field->customfield_value;
					return $priceInput . '</td><td>'.JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', NULL, 'value', 'text', $currentValue) ;
				} else{
					return $priceInput . '</td><td><input type="text" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
					break;
				}

				break;
			/* parent hint, this is a GROUP and should be G not P*/
			case 'G':
				return $field->customfield_value . '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" /></td><td>';
				break;
			/* image */
			case 'M':

				if($field->is_list and $field->is_input){

					$html = $priceInput . '</td><td>is list ';

					$values = explode (';', $field->custom_value);
					foreach($values as $val){
						$html .= $this->displayCustomMedia ($val,'product');
					}
					return $html;
				} else {
					if(empty($field->custom_value)){
						$q = 'SELECT `virtuemart_media_id` as value,`file_title` as text FROM `#__virtuemart_medias` WHERE `published`=1
					AND (`virtuemart_vendor_id`= "' . $virtuemart_vendor_id . '" OR `shared` = "1")';
						$db = JFactory::getDBO();
						$db->setQuery ($q);
						$options = $db->loadObjectList ();
					} else {
						$values = explode (';', $field->custom_value);
						$mM = VmModel::getModel('media');

						foreach ($values as $key => $val) {
							$mM->setId($val);
							$file = $mM->getFile();

							$tmp = array('value' => $val, 'text' => $file->file_name);
							$options[] = (object)$tmp;
						}
					}

					return $priceInput . '</td><td>' . JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', '', 'value', 'text', $field->customfield_value);
				}

				break;

			case 'D':
				return $priceInput . '</td><td>' . vmJsApi::jDate ($field->customfield_value, 'field[' . $row . '][customfield_value]', 'field_' . $row . '_customvalue') ;
				break;

			//'X'=>'COM_VIRTUEMART_CUSTOM_EDITOR',
			case 'X':
        // Not sure why this block is needed to get it to work when editing the customfield (the subsequent block works fine when creating it, ie. in JS)
				$document = JFactory::getDocument();
				if (get_class($document) == 'JDocumentHTML') {
					$editor = JFactory::getEditor();
					return $editor->display('field['.$row.'][customfield_value]',$field->customfield_value, '550', '400', '60', '20', false).'</td><td>';
				}
				return $priceInput . '</td><td><textarea class="mceInsertContentNew" name="field[' . $row . '][customfield_value]" id="field-' . $row . '-customfield_value">' . $field->customfield_value . '</textarea>
						<script type="text/javascript">// Creates a new editor instance
							tinymce.execCommand("mceAddControl",true,"field-' . $row . '-customfield_value")
						</script>';
				//return '<input type="text" value="'.$field->customfield_value.'" name="field['.$row.'][customfield_value]" /></td><td>'.$priceInput;
				break;
			//'Y'=>'COM_VIRTUEMART_CUSTOM_TEXTAREA'
			case 'Y':
				return $priceInput . '</td><td><textarea id="field[' . $row . '][customfield_value]" name="field[' . $row . '][customfield_value]" class="inputbox" cols=80 rows=6 >' . $field->customfield_value . '</textarea>';
				//return '<input type="text" value="'.$field->customfield_value.'" name="field['.$row.'][customfield_value]" /></td><td>'.$priceInput;
				break;
			/*Extended by plugin*/
			case 'E':

				$html = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
				if (!class_exists ('vmCustomPlugin')) {
					require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
				}
				//vmdebug('displayProductCustomfieldBE $field',$field);
				JPluginHelper::importPlugin ('vmcustom', $field->custom_element);
				$dispatcher = JDispatcher::getInstance ();
				$retValue = '';
				$dispatcher->trigger ('plgVmOnProductEdit', array($field, $product_id, &$row, &$retValue));

				return $html . $priceInput   . '</td><td>'. $retValue;
				break;

			/* related category*/
			case 'Z':
				if (!$product_id or empty($field->customfield_value)) {
					return '';
				} // special case it's category ID !

				$q = 'SELECT * FROM `#__virtuemart_categories_' . VmConfig::$vmlang . '` INNER JOIN `#__virtuemart_categories` AS p using (`virtuemart_category_id`) WHERE `virtuemart_category_id`= "' . (int)$field->customfield_value . '" ';
				$db = JFactory::getDBO();
				$db->setQuery ($q);
				//echo $db->_sql;
				if ($category = $db->loadObject ()) {
					$q = 'SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias` WHERE `virtuemart_category_id`= "' . (int)$field->customfield_value . '" ';
					$db->setQuery ($q);
					$thumb = '';
					if ($media_id = $db->loadResult ()) {
						$thumb = $this->displayCustomMedia ($media_id,'category');
					}

					$display = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
					$display .= '<span class="custom_related_image">'.$thumb.'</span><span class="custom_related_title">';
					$display .= JHtml::link ('index.php?option=com_virtuemart&view=category&task=edit&virtuemart_category_id=' . (int)$field->customfield_value, $category->category_name, array('title' => $category->category_name,'target'=>'blank')).'</span>';
					return $display;
				}
				else {
					return 'no result $product_id = '.$product_id.' and '.$field->customfield_value;
				}
			/* related product*/
			case 'R':
				if (!$product_id) {
					return '';
				}

				$pModel = VmModel::getModel('product');
				$related = $pModel->getProduct((int)$field->customfield_value,TRUE,FALSE,FALSE,1);
				if (!empty($related->virtuemart_media_id[0])) {
					$thumb = $this->displayCustomMedia ($related->virtuemart_media_id[0]).' ';
				} else {
					$thumb = $this->displayCustomMedia (0).' ';
				}
				$display = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
				$display .= '<span class="custom_related_image">'.$thumb.'</span><span class="custom_related_title">';
				$display .= JHtml::link ('index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id=' . $related->virtuemart_product_id , $related->product_name, array('title' => $related->product_name,'target'=>'blank')).'</span>';
				return $display;

		}
	}



	/**
	 * @author Max Milbers
	 * @param $product
	 * @param $customfield
	 */
	public function displayProductCustomfieldFE (&$product, &$customfields) {

		if (!class_exists ('calculationHelper')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
		}
		$calculator = calculationHelper::getInstance ();

		$selectList = array();

		$dynChilds = 1; //= array();
		$session = JFactory::getSession ();
		$virtuemart_category_id = $session->get ('vmlastvisitedcategoryid', 0, 'vm');

		foreach($customfields as $k => &$customfield){

			if(!isset($customfield->display))$customfield->display = '';

			$calculator ->_product = $product;
			if (!class_exists ('vmCustomPlugin')) {
				require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
			}

			if ($customfield->field_type == "E") {

				JPluginHelper::importPlugin ('vmcustom');
				$dispatcher = JDispatcher::getInstance ();
				$ret = $dispatcher->trigger ('plgVmOnDisplayProductFEVM3', array(&$product, &$customfield));
				continue;
			}

			$fieldname = 'field['.$product->virtuemart_product_id.'][' . $customfield->virtuemart_customfield_id . '][customfield_value]';
			$customProductDataName = 'customProductData['.$product->virtuemart_product_id.']['.$customfield->virtuemart_custom_id.']';

			//This is a kind of fallback, setting default of custom if there is no value of the productcustom
			$customfield->customfield_value = empty($customfield->customfield_value) ? $customfield->custom_value : $customfield->customfield_value;

			$type = $customfield->field_type;

			$idTag = (int)$product->virtuemart_product_id.'-'.$customfield->virtuemart_customfield_id;
			$idTag = $idTag . 'customProductData';
			$idTag = VmHtml::ensureUniqueId($idTag);

			if (!class_exists ('CurrencyDisplay'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance ();

			switch ($type) {

				case 'C':

					$html = '';

					$dropdowns = array();

					if(isset($customfield->options->{$product->virtuemart_product_id})){
						$productSelection = $customfield->options->{$product->virtuemart_product_id};
					} else {
						$productSelection = false;
					}

					$ignore = array();

					foreach($customfield->options as $product_id=>$variants){


						foreach($variants as $k => $variant){
							//if(in_array($variant,$ignore)){ vmdebug('Product to ignore, continue',$product_id,$k,$variant);continue;}

							if(!isset($dropdowns[$k]) or !is_array($dropdowns[$k])) $dropdowns[$k] = array();
							if(!in_array($variant,$dropdowns[$k])  ){
								if($k==0 or !$productSelection){
									$dropdowns[$k][] = $variant;
								} else if($k>0 and $productSelection[$k-1] == $variants[$k-1]){
									$break=false;
									for($h=1;$h<=$k;$h++){
										if($productSelection[$h-1] != $variants[$h-1]){
											//$ignore[] = $variant;
											$break=true;
										}
									}
									if(!$break){
										$dropdowns[$k][] = $variant;
									}

								} else {
								//	break;
								}
							}
						}
					}

					$tags = array();
					foreach($customfield->selectoptions as $k => $soption){
						$options = array();
						$selected = false;
						foreach($dropdowns[$k] as $i=> $elem){

							$elem = trim((string)$elem);
							$text = $elem;

							if($soption->clabel!='' and in_array($soption->voption,self::$dimensions) ){
								$rd = $soption->clabel;
								if(is_numeric($rd) and is_numeric($elem)){
									$text = number_format(round((float)$elem,(int)$rd),$rd);
								}
								//vmdebug('($dropdowns[$k] in DIMENSION value = '.$elem.' r='.$rd.' '.$text);
							} else if  ($soption->voption === 'clabels' and $soption->clabel!='') {
								$text = vmText::_($elem);
							}

							if($elem=='0'){
								$text = vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION');
							}
							$options[] = array('value'=>$elem,'text'=>$text);

							if($productSelection and $productSelection[$k] == $elem){
								$selected = $elem;
							}
						}

						if(empty($selected)){
							$product->orderable=false;
						}
						$idTagK = $idTag.'cvard'.$k;
						if($customfield->showlabels){
							if( in_array($soption->voption,self::$dimensions) ){
								$soption->slabel = vmText::_('COM_VIRTUEMART_'.strtoupper($soption->voption));
							} else if(!empty($soption->clabel) and !in_array($soption->voption,self::$dimensions) ){
								$soption->slabel = vmText::_($soption->clabel);
							}
							if(isset($soption->slabel)){
								$html .= '<span class="vm-cmv-label" >'.$soption->slabel.'</span>';
							}

						}

						$attribs = array('class'=>'vm-chzn-select cvselection no-vm-bind','data-dynamic-update'=>'1');
						if('productdetails' != vRequest::getCmd('view')){
							$attribs['reload'] = '1';
						}

						$html .= JHtml::_ ('select.genericlist', $options, $fieldname, $attribs , "value", "text", $selected,$idTagK);
						$tags[] = $idTagK;
					}

					$Itemid = vRequest::getInt('Itemid',''); // '&Itemid=127';
					if(!empty($Itemid)){
						$Itemid = '&Itemid='.$Itemid;
					}

					//create array for js
					$jsArray = array();

					$url = '';
					foreach($customfield->options as $product_id=>$variants){
						$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id='.$product_id.$Itemid);
						$jsArray[] = '["'.$url.'","'.implode('","',$variants).'"]';
					}

					vmJsApi::addJScript('cvfind',false,false);

					$jsVariants = implode(',',$jsArray);
					$j = "
						jQuery('#".implode(',#',$tags)."').off('change',Virtuemart.cvFind);
						jQuery('#".implode(',#',$tags)."').on('change', { variants:[".$jsVariants."] },Virtuemart.cvFind);
					";
					$hash = md5(implode('',$tags));
					vmJsApi::addJScript('cvselvars'.$hash,$j,false);

					//Now we need just the JS to reload the correct product
					$customfield->display = $html;
					break;

				case 'A':

					$html = '';
					//if($selectedFound) continue;
					$options = array();
					$productModel = VmModel::getModel ('product');

					//Note by Jeremy Magne (Daycounts) 2013-08-31
					//Previously the the product model is loaded but we need to ensure the correct product id is set because the getUncategorizedChildren does not get the product id as parameter.
					//In case the product model was previously loaded, by a related product for example, this would generate wrong uncategorized children list
					$productModel->setId($customfield->virtuemart_product_id);

					$uncatChildren = $productModel->getUncategorizedChildren ($customfield->withParent);

					if(!$customfield->withParent or ($customfield->withParent and $customfield->parentOrderable)){
						$options[0] = array('value' => JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id=' . $customfield->virtuemart_product_id,FALSE), 'text' => vmText::_ ('COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT'));
					}

					$selected = vRequest::getInt ('virtuemart_product_id',0);
					$selectedFound = false;

					if (empty($calculator) and $customfield->wPrice) {
						if (!class_exists ('calculationHelper'))
							require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
						$calculator = calculationHelper::getInstance ();
					}

					$parentStock = 0;
					foreach ($uncatChildren as $k => $child) {
						if(!isset($child[$customfield->customfield_value])){
							vmdebug('The child has no value at index '.$customfield->customfield_value,$customfield,$child);
						} else {

							$productChild = $productModel->getProduct((int)$child['virtuemart_product_id'],false);
							if(!$productChild) continue;
							$available = $productChild->product_in_stock - $productChild->product_ordered;
							if(VmConfig::get('stockhandle','none')=='disableit_children' and $available <= 0){
								continue;
							}
							$parentStock += $available;
							$priceStr = '';
							if($customfield->wPrice){
								//$product = $productModel->getProductSingle((int)$child['virtuemart_product_id'],false);
								$productPrices = $calculator->getProductPrices ($productChild);
								$priceStr =  ' (' . $currency->priceDisplay ($productPrices['salesPrice']) . ')';
							}
							$options[] = array('value' => JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $virtuemart_category_id . '&virtuemart_product_id=' . $child['virtuemart_product_id']), 'text' => $child[$customfield->customfield_value].$priceStr);
							if($selected==$child['virtuemart_product_id']){
								$selectedFound = true;
								vmdebug($customfield->virtuemart_product_id.' $selectedFound by vRequest '.$selected);
							}
							//vmdebug('$child productId ',$child['virtuemart_product_id'],$customfield->customfield_value,$child);
						}
					}
					if(!$selectedFound){
						$pos = array_search($customfield->virtuemart_product_id, $product->allIds);
						if(isset($product->allIds[$pos-1])){
							$selected = $product->allIds[$pos-1];
							//vmdebug($customfield->virtuemart_product_id.' Set selected to - 1 allIds['.($pos-1).'] = '.$selected.' and count '.$dynChilds);
							//break;
						} elseif(isset($product->allIds[$pos])){
							$selected = $product->allIds[$pos];
							//vmdebug($customfield->virtuemart_product_id.' Set selected to allIds['.$pos.'] = '.$selected.' and count '.$dynChilds);
						} else {
							$selected = $customfield->virtuemart_product_id;
							//vmdebug($customfield->virtuemart_product_id.' Set selected to $customfield->virtuemart_product_id ',$selected,$product->allIds);
						}
					}

					$url = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id='.
						$virtuemart_category_id .'&virtuemart_product_id='. $selected;
					$html .= JHtml::_ ('select.genericlist', $options, $fieldname, 'onchange="window.top.location.href=this.options[this.selectedIndex].value" size="1" class="vm-chzn-select no-vm-bind" data-dynamic-update="1" ', "value", "text",
						JRoute::_ ($url,false),$idTag);

					vmJsApi::chosenDropDowns();

					if($customfield->parentOrderable==0){
						if($product->product_parent_id==0){
							$product->orderable = FALSE;
						} else {
							$product->product_in_stock = $parentStock;
						}

					} else {


					}

					$dynChilds++;
					$customfield->display = $html;
					break;

				/*Date variant*/
				case 'D':
					if(empty($customfield->custom_value)) $customfield->custom_value = 'LC2';
					//Customer selects date
					if($customfield->is_input){
						$customfield->display =  '<span class="product_custom_date">' . vmJsApi::jDate ($customfield->customfield_value,$customProductDataName) . '</span>'; //vmJsApi::jDate($field->custom_value, 'field['.$row.'][custom_value]','field_'.$row.'_customvalue').$priceInput;
					}
					//Customer just sees a date
					else {
						$customfield->display =  '<span class="product_custom_date">' . vmJsApi::date ($customfield->customfield_value, $customfield->custom_value, TRUE) . '</span>';
					}

					break;
				/* text area or editor No vmText, only displayed in BE */
				case 'X':
				case 'Y':
					$customfield->display =  $customfield->customfield_value;
					break;
				/* string or integer */
				case 'B':
				case 'S':
				case 'M':

					if($type== 'M'){
						$selectType = 'select.radiolist';
						$class = '';
					} else {
						$selectType = 'select.genericlist';
						if(!empty($customfield->is_input)){
							vmJsApi::chosenDropDowns();
							$class = 'class="vm-chzn-select"';
						}
					}

					if($customfield->is_list and $customfield->is_list!=2){

						if(!empty($customfield->is_input)){

							$options = array();

							$values = explode (';', $customfield->custom_value);

							foreach ($values as $key => $val) {
								if($type == 'M'){
									$tmp = array('value' => $val, 'text' => $this->displayCustomMedia ($val,'product',$customfield->width,$customfield->height));
									$options[] = (object)$tmp;
								} else {
									$options[] = array('value' => $val, 'text' => vmText::_($val));
								}
							}

							$currentValue = $customfield->customfield_value;

							$customfield->display = JHtml::_ ($selectType, $options, $customProductDataName.'[' . $customfield->virtuemart_customfield_id . ']', $class, 'value', 'text', $currentValue,$idTag);
						} else {
							if($type == 'M'){
								$customfield->display =  $this->displayCustomMedia ($customfield->customfield_value,'product',$customfield->width,$customfield->height);
							} else {
								$customfield->display =  vmText::_ ($customfield->customfield_value);
							}
						}
					} else {

						if(!empty($customfield->is_input)){

							if(!isset($selectList[$customfield->virtuemart_custom_id])) {
								$tmpField = clone($customfield);
								$tmpField->options = null;
								$customfield->options[$customfield->virtuemart_customfield_id] = $tmpField;
								$selectList[$customfield->virtuemart_custom_id] = $k;
								$customfield->customProductDataName = $customProductDataName;
							} else {
								$customfields[$selectList[$customfield->virtuemart_custom_id]]->options[$customfield->virtuemart_customfield_id] = $customfield;
								unset($customfields[$k]);
								//$customfield->options[$customfield->virtuemart_customfield_id] = $customfield;
							}

							$default = reset($customfields[$selectList[$customfield->virtuemart_custom_id]]->options);
							foreach ($customfields[$selectList[$customfield->virtuemart_custom_id]]->options as &$productCustom) {
								$price = self::_getCustomPrice($productCustom->customfield_price, $currency, $calculator);
								if($type == 'M'){
									$productCustom->text = $this->displayCustomMedia ($productCustom->customfield_value,'product',$customfield->width,$customfield->height).' '.$price;
								} else {
									$trValue = vmText::_($productCustom->customfield_value);
									if($productCustom->customfield_value!=$trValue and strpos($trValue,'%1')!==false){
										$productCustom->text = vmText::sprintf($productCustom->customfield_value,$price);
									} else {
										$productCustom->text = $trValue.' '.$price;
									}
								}
							}


							$customfields[$selectList[$customfield->virtuemart_custom_id]]->display = JHtml::_ ($selectType, $customfields[$selectList[$customfield->virtuemart_custom_id]]->options,
								$customfields[$selectList[$customfield->virtuemart_custom_id]]->customProductDataName,
								$class, 'virtuemart_customfield_id', 'text', $default->customfield_value,$idTag);	//*/
						} else {
							if($type == 'M'){
								$customfield->display = $this->displayCustomMedia ($customfield->customfield_value,'product',$customfield->width,$customfield->height);
							} else {
								$customfield->display =  vmText::_ ($customfield->customfield_value);
							}
						}
					}

					break;

				case 'Z':
					if(empty($customfield->customfield_value)) break;
					$html = '';
					$q = 'SELECT * FROM `#__virtuemart_categories_' . VmConfig::$vmlang . '` as l INNER JOIN `#__virtuemart_categories` AS c using (`virtuemart_category_id`) WHERE `published`=1 AND l.`virtuemart_category_id`= "' . (int)$customfield->customfield_value . '" ';
					$db = JFactory::getDBO();
					$db->setQuery ($q);
					if ($category = $db->loadObject ()) {

						if(empty($category->virtuemart_category_id)) break;

						$q = 'SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias`WHERE `virtuemart_category_id`= "' . $category->virtuemart_category_id . '" ';
						$db->setQuery ($q);
						$thumb = '';
						if ($media_id = $db->loadResult ()) {
							$thumb = $this->displayCustomMedia ($media_id,'category',$customfield->width,$customfield->height);
						}
						$customfield->display = JHtml::link (JRoute::_ ('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id), $thumb . ' ' . $category->category_name, array('title' => $category->category_name,'target'=>'_blank'));
					}
					break;
				case 'R':
					if(empty($customfield->customfield_value)){
						$customfield->display = 'customfield related product has no value';
						break;
					}
					$pModel = VmModel::getModel('product');
					$related = $pModel->getProduct((int)$customfield->customfield_value,TRUE,$customfield->wPrice,TRUE,1);

					if(!$related) break;

					$thumb = '';
					if($customfield->wImage){
						if (!empty($related->virtuemart_media_id[0])) {
							$thumb = $this->displayCustomMedia ($related->virtuemart_media_id[0],'product',$customfield->width,$customfield->height).' ';
						} else {
							$thumb = $this->displayCustomMedia (0,'product',$customfield->width,$customfield->height).' ';
						}
					}

					$customfield->display = shopFunctionsF::renderVmSubLayout('related',array('customfield'=>$customfield,'related'=>$related, 'thumb'=>$thumb));

					break;
			}
		}

	}
	/**
	 * There are too many functions doing almost the same for my taste
	 * the results are sometimes slighty different and makes it hard to work with it, therefore here the function for future proxy use
	 *
	 */
	static public function displayProductCustomfieldSelected ($product, $html, $trigger) {

		if(isset($product->param)){
			vmTrace('param found, seek and destroy');
			return false;
		}
		$row = 0;
		if (!class_exists ('shopFunctionsF'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');

		$variantmods = isset($product -> customProductData)?$product -> customProductData:$product -> product_attribute;

		if(empty($variantmods)){
			$productDB = VmModel::getModel('product')->getProduct($product->virtuemart_product_id);
			if($productDB){
				$product->customfields = $productDB->customfields;
			}
		}
		if(!is_array($variantmods)){
			$variantmods = json_decode($variantmods,true);
		}

		$productCustoms = array();
		foreach( (array)$product->customfields as $prodcustom){

			//We just add the customfields to be shown in the cart to the variantmods
			if(is_object($prodcustom)){
				if($prodcustom->is_cart_attribute and !$prodcustom->is_input){
					if(!is_array($variantmods[$prodcustom->virtuemart_custom_id])){
						$variantmods[$prodcustom->virtuemart_custom_id] = array();
					}
					$variantmods[$prodcustom->virtuemart_custom_id][$prodcustom->virtuemart_customfield_id] = false;

				} else if(!empty($variantmods) and !empty($variantmods[$prodcustom->virtuemart_custom_id])){

				}
				$productCustoms[$prodcustom->virtuemart_customfield_id] = $prodcustom;
			}
		}

		foreach ( (array)$variantmods as $custom_id => $customfield_ids) {

			if(!is_array($customfield_ids)){
				$customfield_ids = array( $customfield_ids =>false);
			}

			foreach($customfield_ids as $customfield_id=>$params){

				if(empty($productCustoms) or !isset($productCustoms[$customfield_id])){
					continue;
				}
				$productCustom = $productCustoms[$customfield_id];
				//The stored result in vm2.0.14 looks like this {"48":{"textinput":{"comment":"test"}}}
				//and now {"32":[{"invala":"100"}]}
				if (!empty($productCustom)) {
					$otag = ' <span class="product-field-type-' . $productCustom->field_type . '">';
					if ($productCustom->field_type == "E") {

						$tmp = '';
						if (!class_exists ('vmCustomPlugin'))
							require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
						JPluginHelper::importPlugin ('vmcustom');
						$dispatcher = JDispatcher::getInstance ();
						$dispatcher->trigger ($trigger.'VM3', array(&$product, &$productCustom, &$tmp));
						if(!empty($tmp)){
							$html .= $otag.$tmp;
						}
					}
					else {
						$value = '';
						if (($productCustom->field_type == 'G')) {
							$db = JFactory::getDBO ();
							$db->setQuery ('SELECT  `product_name` FROM `#__virtuemart_products_' . VmConfig::$vmlang . '` WHERE virtuemart_product_id=' . (int)$productCustom->customfield_value);
							$child = $db->loadObject ();
							$value = $child->product_name;
						}
						elseif (($productCustom->field_type == 'M')) {
							$customFieldModel = VmModel::getModel('customfields');
							$value = $customFieldModel->displayCustomMedia ($productCustom->customfield_value,'product',$productCustom->width,$productCustom->height,self::$useAbsUrls);
						}
						elseif (($productCustom->field_type == 'S')) {
							if($productCustom->is_list and $productCustom->is_input){
								$value = vmText::_($params);
							} else {
								$value = vmText::_($productCustom->customfield_value);
							}
						}
						elseif (($productCustom->field_type == 'A')) {
							$value = vmText::_( $product->{$productCustom->customfield_value} );
							//vmdebug('Customfield A',$productCustom,$productCustom->customfield_value);
						}
						elseif (($productCustom->field_type == 'C')) {

							//vmdebug('displayProductCustomfieldSelected C',$productCustom,$productCustom->selectoptions);
							foreach($productCustom->options->{$product->virtuemart_product_id} as $k=>$option){
								$value .= '<span> ';
								if(!empty($productCustom->selectoptions[$k]->clabel) and in_array($productCustom->selectoptions[$k]->voption,self::$dimensions)){
									$value .= vmText::_('COM_VIRTUEMART_'.$productCustom->selectoptions[$k]->voption);
									$rd = $productCustom->selectoptions[$k]->clabel;
									if(is_numeric($rd) and is_numeric($option)){
										$value .= ' '.number_format(round((float)$option,(int)$rd),$rd);
									}
								} else {
									if(!empty($productCustom->selectoptions[$k]->clabel)) $value .= vmText::_($productCustom->selectoptions[$k]->clabel);
									$value .= ' '.vmText::_($option).' ';
								}
								$value .= '</span><br>';
							}
							$value = trim($value);
							if(!empty($value)){
								$html .= $otag.$value.'</span><br />';
							}

							continue;
						}
						else {
							$value = vmText::_($productCustom->customfield_value);
						}
						$trTitle = vmText::_($productCustom->custom_title);
						$tmp = '';
						if($productCustom->custom_title!=$trTitle and strpos($trTitle,'%1')!==false){
							$tmp .= vmText::sprintf($productCustom->custom_title,$value);
						} else {
							$tmp .= $trTitle.' '.$value;
						}
						if(!empty($tmp)){
							$html .= $otag.$tmp.'</span><br />';
						}
					}


				}
				else {
					foreach ((array)$customfield_id as $key => $value) {
						$html .= '<br/ >Couldnt find customfield' . ($key ? '<span>' . $key . ' </span>' : '') . $value;
					}
					vmdebug ('customFieldDisplay, $productCustom is EMPTY '.$customfield_id);
				}
			}

		}

		return $html . '</div>';
	}


	/**
	 * TODO This is html and view stuff and MUST NOT be in the model, notice by Max
	 * render custom fields display cart module FE
	 */
	static public function CustomsFieldCartModDisplay ($product) {
		return self::displayProductCustomfieldSelected ($product, '<div class="vm-customfield-mod">', 'plgVmOnViewCartModule');
	}

	/**
	 * render custom fields display cart FE
	 */
	static public function CustomsFieldCartDisplay ($product) {
		return self::displayProductCustomfieldSelected ($product, '<div class="vm-customfield-cart">', 'plgVmOnViewCart');
	}

	/**
	 * render custom fields display order BE/FE
	 */
	static public function CustomsFieldOrderDisplay ($item, $view = 'FE', $absUrl = FALSE) {
		if (!empty($item->product_attribute)) {
			$item->customProductData = json_decode ($item->product_attribute, TRUE);
		}
		return self::displayProductCustomfieldSelected ($item, '<div class="vm-customfield-cart">', 'plgVmDisplayInOrder' . $view);
	}

	function displayCustomMedia ($media_id, $table = 'product', $width = false, $height = false, $absUrl = false) {

		if (!class_exists ('TableMedias'))
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'medias.php');

		$db = JFactory::getDBO ();
		$data = new TableMedias($db);
		$data->load ((int)$media_id);
		if(!empty($data->file_type)){
			$table = $data->file_type;
		}

		if (!class_exists ('VmMediaHandler'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'mediahandler.php');
		$media = VmMediaHandler::createMedia ($data, $table);

		if(!$width) $width = VmConfig::get('img_width',90);
		if(!$height) $height = VmConfig::get('img_height',90);

		return $media->displayMediaThumb ('', FALSE, '', TRUE, TRUE, $absUrl, $width, $height);
	}

	static function _getCustomPrice($customPrice, $currency, $calculator) {
		if ((float)$customPrice) {
			$price = strip_tags ($currency->priceDisplay ($calculator->calculateCustomPriceWithTax ($customPrice)));
			if ($customPrice >0) {
				$price ="+".$price;
			}
		}
		else {
			$price = ($customPrice === '') ? '' :  vmText::_ ('COM_VIRTUEMART_CART_PRICE_FREE');
		}
		return $price;
	}

	/**
	 * @param $product
	 * @param $variants ids of the selected variants
	 * @return float
	 */
	public function calculateModificators(&$product) {

		$modificatorSum = 0.0;

		//VmConfig::$echoDebug=true;
		if (!empty($product->customfields)){

			foreach($product->customfields as $k=>$productCustom){
				$selected = -1;

				if(isset($product->cart_item_id)){
					if (!class_exists('VirtueMartCart'))
						require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
					$cart = VirtueMartCart::getCart();

					//vmdebug('my $productCustom->customfield_price '.$productCustom->virtuemart_customfield_id,$cart->cartProductsData,$cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id]);
					if(isset($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])){
						$selected = $cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];

					} else if( isset($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id])){
						if($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id]== $productCustom->virtuemart_customfield_id){
							$selected = $productCustom->virtuemart_customfield_id;	//= 1;

						}
					}
					//vmdebug('my $productCustom->customfield_price',$selected,$productCustom->virtuemart_custom_id,$productCustom->virtuemart_customfield_id,$cart->cartProductsData[$product->cart_item_id]['customProductData']);
				} else {

					$pluginFields = vRequest::getVar ('customProductData', NULL);

					if ($pluginFields == NULL and isset($product->customPlugin)) {
						$pluginFields = json_decode ($product->customPlugin, TRUE);
					}

					if(isset($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])){
						$selected = $pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];
					} else if (isset($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id])){
						if($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id]== $productCustom->virtuemart_customfield_id){
							$selected = 1;
						}

					}
				}

				if($selected === -1) {
					continue;
				}

				if (!empty($productCustom) and $productCustom->field_type =='E') {

					if(!class_exists('vmCustomPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmcustomplugin.php');
					JPluginHelper::importPlugin('vmcustom');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('plgVmPrepareCartProduct',array(&$product, &$product->customfields[$k],$selected,&$modificatorSum));
				} else {
					if ($productCustom->customfield_price) {
						//vmdebug('calculateModificators $productCustom->customfield_price ',$productCustom->customfield_price);
						//TODO adding % and more We should use here $this->interpreteMathOp
						$modificatorSum = $modificatorSum + $productCustom->customfield_price;
					}
				}
			}
		}

		return $modificatorSum;
	}


	/** Save and delete from database
	* all product custom_fields and xref
	@ var   $table	: the xref table(eg. product,category ...)
	@array $data	: array of customfields
	@int     $id		: The concerned id (eg. product_id)
	*/
	public function storeProductCustomfields($table,$datas, $id) {

		vRequest::vmCheckToken('Invalid token in storeProductCustomfields');
		//Sanitize id
		$id = (int)$id;

		//Table whitelist
		$tableWhiteList = array('product','category','manufacturer');
		if(!in_array($table,$tableWhiteList)) return false;

		// Get old IDS
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_'.$table.'_customfields` as `PC` WHERE `PC`.virtuemart_'.$table.'_id ='.$id );
		$old_customfield_ids = $db->loadColumn();
		if (array_key_exists('field', $datas)) {

			foreach($datas['field'] as $key => $fields){

				if(!empty($datas['field'][$key]['virtuemart_product_id']) and (int)$datas['field'][$key]['virtuemart_product_id']!=$id){
					//aha the field is from the parent, what we do with it?
					$fields['override'] = (int)$fields['override'];
					$fields['disabler'] = (int)$fields['disabler'];
					if($fields['override']!=0 or $fields['disabler']!=0){
						//If it is set now as override, store it as clone, therefore set the virtuemart_customfield_id = 0
						if($fields['override']!=0){
							$fields['override'] = $fields['virtuemart_customfield_id'];
						}
						if($fields['disabler']!=0){
							$fields['disabler'] = $fields['virtuemart_customfield_id'];
						}
						$fields['virtuemart_customfield_id'] = 0;
					}
					else {
						//we do not store customfields inherited by the parent, therefore
						$key = array_search($fields['virtuemart_customfield_id'], $old_customfield_ids );
						if ($key !== false ){
							unset( $old_customfield_ids[ $key ] );
						}
						continue;
					}
				}

				if($fields['field_type']=='C'){
					$cM = VmModel::getModel('custom');
					$c = $cM->getCustom($fields['virtuemart_custom_id'],'');

					if(!empty($c->sCustomId)){

						$sCustId = $c->sCustomId;
						$labels = array();
						foreach($fields['selectoptions'] as $k => $option){
							if($option['voption'] == 'clabels' and !empty($option['clabel'])){
								$labels[$k] = $option['clabel'];
							}
						}

						//for testing
						foreach($fields['options'] as $prodId => $lvalue){
							if($prodId == $id) continue;
							$db->setQuery( 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_'.$table.'_customfields` as `PC` WHERE `PC`.virtuemart_'.$table.'_id ="'.$prodId.'" AND `virtuemart_custom_id`="'.$sCustId.'" '  );
							$strIds = $db->loadColumn();
							$i=0;
							foreach($lvalue as $k=>$value) {

								if(!empty($labels[$k])) {
									$ts = array();
									$ts['field_type'] = 'S';
									$ts['virtuemart_product_id'] = $prodId;
									$ts['virtuemart_custom_id'] = $sCustId;
									if(isset($strIds[$i])){
										$ts['virtuemart_customfield_id'] = $strIds[$i];
										unset( $strIds[$i++] );
									}
									$ts['customfield_value'] = $value;

									$tableCustomfields = $this->getTable($table.'_customfields');
									$tableCustomfields->bindChecknStore($ts);
								}
							}

							if(count($strIds)>0){
								// delete old unused Customfields
								$db->setQuery( 'DELETE FROM `#__virtuemart_'.$table.'_customfields` WHERE `virtuemart_customfield_id` in ("'.implode('","', $strIds ).'") ');
								$db->execute();
							}
						}
					}
				}

				$fields['virtuemart_'.$table.'_id'] = $id;
				$tableCustomfields = $this->getTable($table.'_customfields');
				$tableCustomfields->setPrimaryKey('virtuemart_product_id');
				if (!empty($datas['customfield_params'][$key]) and !isset($datas['clone']) ) {
					if (array_key_exists( $key,$datas['customfield_params'])) {
						$fields = array_merge ((array)$fields, (array)$datas['customfield_params'][$key]);
					}
				}
				$tableCustomfields->_xParams = 'customfield_params';
				if(!class_exists('VirtueMartModelCustom')) require(VMPATH_ADMIN.DS.'models'.DS.'custom.php');
				VirtueMartModelCustom::setParameterableByFieldType($tableCustomfields,$fields['field_type'],$fields['custom_element'],$fields['custom_jplugin_id']);

				$tableCustomfields->bindChecknStore($fields);

				$key = array_search($fields['virtuemart_customfield_id'], $old_customfield_ids );
				if ($key !== false ) unset( $old_customfield_ids[ $key ] );

			}
		} else {
			vmdebug('storeProductCustomfields nothing to store');
		}
		vmdebug('Delete $old_customfield_ids',$old_customfield_ids);
		if ( count($old_customfield_ids) ) {
			// delete old unused Customfields
			$db->setQuery( 'DELETE FROM `#__virtuemart_'.$table.'_customfields` WHERE `virtuemart_customfield_id` in ("'.implode('","', $old_customfield_ids ).'") ');
			$db->execute();
			vmdebug('Deleted $old_customfield_ids',$old_customfield_ids);
		}


		JPluginHelper::importPlugin('vmcustom');
		$dispatcher = JDispatcher::getInstance();
		if (isset($datas['customfield_params']) and is_array($datas['customfield_params'])) {
			foreach ($datas['customfield_params'] as $key => $plugin_param ) {
				$dispatcher->trigger('plgVmOnStoreProduct', array($datas, $plugin_param ));
			}
		}

	}

	static public function setEditCustomHidden ($customfield, $i) {

		if (!isset($customfield->virtuemart_customfield_id))
			$customfield->virtuemart_customfield_id = '0';
		if (!isset($customfield->virtuemart_product_id))
			$customfield->virtuemart_product_id = '';
		$html = '
			<input type="hidden" value="' . $customfield->field_type . '" name="field[' . $i . '][field_type]" />
			<input type="hidden" value="' . $customfield->custom_element . '" name="field[' . $i . '][custom_element]" />
			<input type="hidden" value="' . $customfield->custom_jplugin_id . '" name="field[' . $i . '][custom_jplugin_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_custom_id . '" name="field[' . $i . '][virtuemart_custom_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_product_id . '" name="field[' . $i . '][virtuemart_product_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_customfield_id . '" name="field[' . $i . '][virtuemart_customfield_id]" />';
			$html .= '<input class="ordering" type="hidden" value="'.$customfield->ordering.'" name="field['.$i .'][ordering]" />';
		return $html;

	}

	private $_hidden = array();

	public function addHidden ($name, $value = '') {
		$this->_hidden[$name] = $value;
	}

}
// pure php no closing tag
