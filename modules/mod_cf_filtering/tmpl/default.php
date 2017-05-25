<?php
/**
 * @version		$Id: default.php 1 2014-02-19 16:44 sakis Terz $
 * @package		customfilters
 * @subpackage	mod_cf_filtering
 * @copyright	Copyright (C) 2010 - 2014 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC')or die;
JHtml::_('behavior.framework', true);
JHTML::_('behavior.calendar');

$document=JFactory::getDocument();
$jinput=JFactory::getApplication()->input;
$view=$jinput->get('view','products','cmd');
$component=$jinput->get('option','','cmd');
$direction=$document->getDirection();
$menu_params=cftools::getMenuparams();
$Itemid=$menu_params->get('cf_itemid','');
$results_trigger=$params->get('results_trigger','sel');
$results_loading_mode=$params->get('results_loading_mode','http');
$jconfig=JFactory::getConfig();
$issef=$jconfig->get('sef');
$filters_html_array=array();


$document->addScript(JURI::root().'modules/mod_cf_filtering/assets/general.js');
$document->addScript(JURI::root().'media/system/js/modal.js');
$document->addStyleSheet(JURI::root().'modules/mod_cf_filtering/assets/style.css');


/*CSS for RTL sites in  webkit browser*/
$browser = &JBrowser::getInstance();
$browserType = $browser->getBrowser();
if(($browserType=='chrome' || $browserType=='safari') &&  $direction=='rtl'){
	$style='.knob_wrapper {margin-left:18px;}';
}
if(!empty($style))$document->addStyleDeclaration($style); 
if(!empty($filters_render_array['stylesDeclaration']))$document->addStyleDeclaration($filters_render_array['stylesDeclaration']); 



if(!empty($filters_render_array['html']))$filters_html_array=$filters_render_array['html'];
if(isset($filters_render_array['resetUri']))$resetUri=$filters_render_array['resetUri'];

$expanded_state=!isset($filters_render_array['expanded_state'])?true:$filters_render_array['expanded_state'];


if(count($filters_html_array)>0){
/*
 * view == module is used only when the module is loaded with ajax. 
 * We want only the form to be loaded with ajax requests. 
 * The cf_wrapp_all of the primary module, will be used as the container of the ajax response   
 */
	if($view!='module'){?>
	<div id="cf_wrapp_all_<?php echo $module->id ?>" class="cf_wrapp_all">
	<?php } 
?>
<div id="cf_ajax_loader_<?php echo $module->id?>"></div>
<form method="get" action="<?php echo JRoute::_('index.php?option=com_customfilters&view=products&Itemid='.$Itemid)?>" class="cf_form<?php echo $moduleclass_sfx;?>" id="cf_form_<?php echo $module->id?>">
	<?php 

	foreach($filters_html_array as $key=>$flt_html){?> 
	<div class="cf_flt_wrapper  cf_flt_wrapper_id_<?php echo $module->id?> cf_flt_wrapper_<?php echo $direction ?>" id="cf_flt_wrapper_<?php echo $key?>">
	
	<?php //if there is a header
	if(isset($filter_headers_array[$key])):
		//toggle state
		if(isset($expanded_state[$key])){
			if($expanded_state[$key]==1)$state='show';
			else $state='hide';
		}else $state='show';
	?>

		<div class="cf_flt_header" id="cfhead_<?php echo $key?>">
			<div class="headexpand headexpand_<?php echo $state?>"	id="headexpand_<?php echo $key?>"></div>
				<?php echo $filter_headers_array[$key]?>
		</div>		
		<?php		
		//add a script for the toggle			
		$filters_render_array['scriptProcesses'][]="customFilters.createToggle('".$key."','$state');";
		endif; ?>
		
		<div class="cf_wrapper_inner" id="cf_wrapper_inner_<?php echo $key?>">
			<?php echo $flt_html?>
		</div>
	</div>
	<?php
	}
	unset($flt_html);
	
	//reset all link
	if(!empty($resetUri)){?>
	<a class="cf_resetAll_link" rel="nofollow" data-module-id="<?php echo $module->id?>" href="<?php echo JRoute::_($resetUri)?>">
		<span class="cf_resetAll_label"><?php echo JText::_('MOD_CF_RESET_ALL')?></span>
	</a>
	<?php 
	}?>
					
		<?php 
		//if no category filter and category var. It means that we are in a category page and the category id should be kept
		if(empty($filters_html_array['virtuemart_category_id_'.$module->id]) && !empty($filters_render_array['selected_flt']['virtuemart_category_id'])):
			foreach($filters_render_array['selected_flt']['virtuemart_category_id'] as $key=>$id){?>
				<input type="hidden" name="virtuemart_category_id[<?php echo $key?>]" value="<?php echo $id?>"/>
			<?php 
			}
		endif;
		
		//if no manufacturer filter and manufact. var. It means that we are in a manufact page and the manufact id should be kept
		if(empty($filters_html_array['virtuemart_manufacturer_id_'.$module->id]) && !empty($filters_render_array['selected_flt']['virtuemart_manufacturer_id'])):
			foreach($filters_render_array['selected_flt']['virtuemart_manufacturer_id'] as $key=>$id){?>
				<input type="hidden" name="virtuemart_manufacturer_id[<?php echo $key?>]" value="<?php echo $id?>"/>
			<?php 
			}
		endif;		
				
		
		//if the keyword search does not exist we have to add it as hidden, because it may added by the search mod
		 if(empty($filters_html_array['q_'.$module->id])): 
		 	$query=!empty($filters_render_array['selected_flt']['q'])?$filters_render_array['selected_flt']['q']:'';?>
		 	<input name="q" type="hidden" value="<?php echo $query;?>"/>
		 <?php 
		 endif;
		
		if(!$issef && $results_loading_mode!='ajax'):?> 	
			<input type="hidden" name="option" value="com_customfilters"/>
			<input type="hidden" name="view" value="products"/>
			<?php 		
			if($Itemid):?><input type="hidden" name="Itemid" value="<?php echo $Itemid?>"/>
			<?php 
			endif;
		endif;
				
		//in case of button add some extra vars to the form
		if($results_trigger=='btn'):?>	
		<input type="submit" class="cf_apply_button btn btn-primary"  id="cf_apply_button_<?php echo $module->id?>" value="<?php echo JText::_('MOD_CF_APPLY');?>"/>
		<?php 
		endif;
		?>
		
		
</form>
<?php 
if($view!='module'){?>
	</div>
	<?php }

	
	//Scripts
	//load the VM scripts and styles in pages other than VM and CF when ajax is used
	if($params->get('results_loading_mode','ajax')=='ajax' && $component!='com_customfilters' || $component!='com_virtuemart' || ($component=='com_virtuemart' && $view!='category')){
		cftools::loadScriptsNstyles();
	}
	
	if(!empty($filters_render_array['scriptVars'])){
		$script_var_counter=count($filters_render_array['scriptVars']);
		$j=1;
		$script='
		if(typeof customFiltersProp=="undefined")customFiltersProp=new Array();
		customFiltersProp['.$module->id.']={';
		foreach($filters_render_array['scriptVars'] as $varName=>$value){
			$script.="$varName:'$value'";			
			if($j<$script_var_counter)$script.=','; //add a comma
			$j++;
		}
		$script.='};';
		$document->addScriptDeclaration($script);
	}
	
	if(!empty($filters_render_array['scriptProcesses'])){
		$script="window.addEvent('domready',function(){";
		foreach($filters_render_array['scriptProcesses'] as $process){
			$script.=$process;
		}
		$script.="});";	
		
		if($view=='module' && $component=='com_customfilters')	echo '<script type="text/javascript">'.$script.'</script>';
		else $document->addScriptDeclaration($script);		
	}
	
	

	//add some script files if exist
	if(!empty($filters_render_array['scriptFiles'])){
		foreach($filters_render_array['scriptFiles'] as $file){
			$document->addScript($file);
		}		
	}


} ?>
