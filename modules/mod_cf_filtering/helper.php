<?php
/**
 * @version		$Id: helper.php 2014-09-22 13:45 sakis Terz $2
 * @package		customfilters
 * @subpackage	mod_cf_filtering
 * @copyright	Copyright (C) 2010 - 2014 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * The module helper class which contains the whole module's logic
 * @package	customfilters
 * @author 	Sakis Terz
 * @since	1.0
 *
 */
class ModCfFilteringHelper{

	//the selected criteria will be stored in this assoc array
	public $selected_flt=array();
	//stores the selections that each filter uses in dependency top-bottom
	public $selected_fl_per_flt=array();
	//remove the inactive from this array
	public $selected_flt_modif=array();
	//the display types of each filter will be stored in this assoc array
	public $disp_types=array();
	//an assoc array that stores the data of all the filters
	public $filters_opt=array();
	//stores the options that are innactive and selected
	public $inactive_select_opt=array();
	//this array contains the header string of every filter in a different list item
	public	$filters_headers_array=array();
	//the option helper class
	public $optHelper;
	//the module parameters
	public $moduleparams;
	//hold the smartSearch options for each filter
	public $smartSearch;
	//hold the expanded/collapsed state of each filter
	public $expanded;
	//it holds info about the current currency
	public $currency_info;
	//styles declaration
	public $stylesDeclaration='';
	//contains any variable which will be passed to the script
	public $scriptVars=array();
	//contains the script files which will be loaded
	public $scriptFiles=array();
	//contains the functions/operations which will be executed in a domready event
	public $scriptProcesses=array();
	//contains the suffixes of the filters
	public $fltSuffix=array(	
	'virtuemart_category_id'=>'category_flt',
	'virtuemart_manufacturer_id'=>'manuf_flt');
	//reset tool active/inactive (bool)
	public $reset=false;
	//text direction
	public $direction='ltr';
	//the current module object
	public $module;
	//mode (on click or with btn)
	public $results_trigger;
	//reults loading mode (http or ajax)
	public $results_loading_mode;
	//the current active trees
	public $active_tree=array();
	//array that contains the ranges
	public $rangeVars=array();
	//component params
	public $component_params;
	//menu params (cf menu)
	public $menu_params;



	/**
	 * The function that trigers other functions for whole module generation
	 * @author	Sakis Terz
	 * @return	array	An array with the inside html code of every filter
	 * @since 	1.0
	 */
	public function getFilters($params, $module){
		$this->module=$module;
		$this->results_trigger=$params->get('results_trigger','sel');
		$this->results_loading_mode=$params->get('results_loading_mode','ajax');
		$japplication=JFactory::getApplication();
		$jinput=$japplication->input;
		$doc= JFactory::getDocument();
		$this->direction=$doc->getDirection();
		$this->scriptVars['base_url']=JURI::base();
		$this->scriptVars['cf_direction']=$this->direction;
		$this->scriptVars['results_trigger']=$this->results_trigger;
		$this->scriptVars['results_wrapper']=$params->get('results_wrapper','bd_results');
		if($this->results_loading_mode=='ajax' || $this->results_trigger=='btn')$loadAjaxModule=true;
		else $loadAjaxModule=false;
		$this->scriptVars['loadModule']=$loadAjaxModule;
		$this->component_params  = cftools::getComponentparams();
		$this->menu_params=cftools::getMenuparams();

		$dependency_dir=$params->get('dependency_direction','all');

		//profiler to get performance benchmarking
		$profilerParam=$params->get('cf_profiler',0);
		if($profilerParam)$profiler=JProfiler::getInstance('application');

		//this array contains the html of every filter in a different list item
		$filters_rendering_array=array();
		//the params
		$this->moduleparams=$params;
		//the selected filters' options array;
		$selected_flt=CfInput::getInputs();
		$this->selected_flt=cftools::encodeInput($selected_flt);
		//holds the selections which should be used for each filter,when the dependency is from-top to bottom
		if(count($this->selected_flt)>0 && $dependency_dir=='t-b')$this->selected_fl_per_flt=cftools::encodeInput(CfInput::getInputsPerFilter($module),$perfilter=true);
		//the helper that contains the logic for retreiving the filters' options
		$this->optHelper=new ModCfilteringOptions($params,$module);
		//check the state of languagefilter plugin.If its active we should join the products language table to find the active options
		$plugin =& JPluginHelper::getPlugin('system', 'languagefilter');
		if(!empty($plugin))$this->optHelper->setLanguageSwitch(true);
		//reset options
		$display_reset_all=$params->get('disp_reset_all',1);
		//check if reset is active
		$this->reset=$jinput->get('reset',0,'int');


		//__________generate filters______________//

		$filters_order=json_decode(str_replace("'", '"', $params->get('filterlist','')));
		$filters_order=(array)$filters_order;
		if(empty($filters_order) || !in_array('virtuemart_category_id', $filters_order) || count($filters_order)!=count($this->fltSuffix))
		$filters_order=array('virtuemart_category_id','virtuemart_manufacturer_id');
		//if its old replace the 'product_price' with 'price'
		$index=array_search('product_price', $filters_order);
		if($index!==false){
			unset($filters_order[$index]);
			$filters_order[$index]='price';
			ksort($filters_order);
		}

		foreach($filters_order as $filter_key){
			//foreach($filters_order)
			switch($filter_key){				

					//--Categories--
				case 'virtuemart_category_id':
					if($params->get('category_flt_published')){
						$key=$filter_key;
						$display_key=$key.'_'.$module->id;//used as key to the html code
						//the categories display type
						$vm_cat_disp_type=$params->get('category_flt_disp_type');
						$this->disp_types[$key]=$vm_cat_disp_type;

						if($vm_cat_disp_type!=3)$vmcat_header=JText::_('MOD_CF_CATEGORY');
						else $vmcat_header=JText::_('MOD_CF_CATEGORIES');						
						$this->setFilter($name=$key,$vmcat_header,false);

						//display headers only in displays other than select drop down
						if(isset($this->filters_opt[$key])){
							$this->filters_headers_array[$display_key]=$vmcat_header;
							if($vm_cat_disp_type!=1){
								//set some styles for the category tree
								if(!$params->get('category_flt_tree_mode',0)){
									$category_flt_collapsed_icon=$params->get('category_flt_collapsed_icon','');
									$category_flt_expanded_icon=$params->get('category_flt_expanded_icon','');
									$category_flt_icon_position=$params->get('category_flt_icon_position','left');

									if($category_flt_collapsed_icon){
										//get the width of the image
										$img_size=getimagesize($category_flt_collapsed_icon);
										if(is_array($img_size))$img_width=$img_size[0]+2;
										else $img_width=16;
										$style='';
										if($category_flt_icon_position=='left'){
											$style.="padding-left:".$img_width."px !important;";
										}
										else {
											if($this->direction=='rtl') $style.="padding-right:".$img_width."px !important;";
											$parent_decl='#cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_parentOpt{display:block; width:90%;}';
										}

										//unexpand
										$style.='background-image:url('.JURI::base().$category_flt_collapsed_icon.') !important;';
										$style.='background-position:'.$category_flt_icon_position.' center !important;';
										$style.='background-repeat:no-repeat !important;';
										$this->stylesDeclaration.='#cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_unexpand{'.$style.'} #cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_unexpand:hover{'.$style.'}' ;
									}
									if($category_flt_expanded_icon){
										//get the width of the image
										$img_size=getimagesize($category_flt_expanded_icon);
										if(is_array($img_size))$img_width=$img_size[0]+2;
										else $img_width=16;
										$style='';
										if($category_flt_icon_position=='left'){
											$style.="padding-left:".$img_width."px !important;";
										}
										else {
											if($this->direction=='rtl') $style.="padding-right:".$img_width."px !important;";
											if(empty($parent_decl))$parent_decl='#cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_parentOpt{display:block; width:90%;}';
										}

										//expand
										$style.='background-image:url('.JURI::base().$category_flt_expanded_icon.') !important;';
										$style.='background-position:'.$category_flt_icon_position.' center !important;';
										$style.='background-repeat:no-repeat !important;';
										$this->stylesDeclaration.='#cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_expand{'.$style.'} #cf_flt_wrapper_virtuemart_category_id_'.$module->id.' .cf_expand:hover{'.$style.'}';
									}
									//styling for all the states
									if(!empty($parent_decl))$this->stylesDeclaration.=$parent_decl;

								}
								//store some params
								$maxHeight=$params->get('category_flt_scrollbar_after','');
								if($maxHeight)$this->stylesDeclaration.=" #cf_list_$display_key { max-height:$maxHeight; overflow:auto; height:auto;}";
							}
						}

						if($profilerParam)$profiler->mark('vm_categories');
					}
					break;

					//--Manufacturers--
				case 'virtuemart_manufacturer_id':
						if($params->get('manuf_flt_published')){  
						$key=$filter_key;
						$display_key=$key.'_'.$module->id;//used as key to the html code
						//-params-
						$vm_manuf_disp_type=$params->get('manuf_flt_disp_type');
						$this->disp_types[$key]=$vm_manuf_disp_type;

						if($vm_manuf_disp_type!=1){							
							$maxHeight=$params->get('manuf_flt_scrollbar_after','');
							if($maxHeight)$this->stylesDeclaration.=" #cf_list_$display_key { max-height:$maxHeight; overflow:auto; height:auto;}";
						}

						if($vm_manuf_disp_type!=3)$mnf_header=JText::_('MOD_CF_MANUFACTURER');
						else $mnf_header=JText::_('MOD_CF_MANUFACTURERS');
						$this->setFilter($name=$key,$mnf_header,false);


						//display headers only in displays other than select drop down
						if(isset($this->filters_opt[$key])){
							$this->filters_headers_array[$display_key]=$mnf_header;							
						}
						if($profilerParam)$profiler->mark('vm_manufs');
					}
					break;				
			}//switch
		}//foreach
		//print in the screen the performance metrics
		if($profilerParam)cftools::printProfiler($profiler);

		if(count($this->filters_opt)>0)	{
			$parentScript='';
			$this->scriptVars['parent_link']=$params->get('category_flt_parent_link',0);

			//set ajax spinners
			if($params->get('use_ajax_spinner','')){
				$spinnerstyle='background-image:url('.JURI::base().$params->get('use_ajax_spinner','').') !important;';
				$spinnerstyle.='background-position:center center;';
				$spinnerstyle.='background-repeat:no-repeat !important;';
				$this->stylesDeclaration.='.cf_ajax_loader{'.$spinnerstyle.'}';
				$ajax_module_spinner=1;
			}else $ajax_module_spinner=0;

			if($params->get('use_results_ajax_spinner','')){
				$spinnerstyle='background-image:url('.JURI::base().$params->get('use_results_ajax_spinner','').') !important;';
				$spinnerstyle.='background-repeat:no-repeat !important;';
				$this->stylesDeclaration.='#cf_res_ajax_loader{'.$spinnerstyle.'}';
				$ajax_results_spinner=1;
			}else $ajax_results_spinner=0;
				
			$this->scriptVars['mod_type']='filtering';
			$this->scriptVars['use_ajax_spinner']=$ajax_module_spinner;
			$this->scriptVars['use_results_ajax_spinner']=$ajax_results_spinner;
			$this->scriptVars['results_loading_mode']=$params->get('results_loading_mode','http');
			$this->scriptVars['category_flt_parent_link']=$params->get('category_flt_parent_link',0);

			if($dependency_dir=='t-b'){}
			else $this->selected_flt_modif=$this->removeInactiveOpt();



			//----------render the filters------------------//
			$selected_flt=array('selected_flt'=>$this->selected_flt,'selected_flt_modif'=>$this->selected_flt_modif,'selected_fl_per_flt'=>$this->selected_fl_per_flt);
			$renderer=new ModCfilteringRender($this->module,$selected_flt,$this->filters_opt);
			$filters_html=$renderer->renderFilters();
			$render_scriptAssets=$renderer->getScriptAssets();
			$this->scriptProcesses=array_merge($this->scriptProcesses,$render_scriptAssets['scriptProcesses']);
			$this->scriptFiles=array_merge($this->scriptFiles,$render_scriptAssets['scriptFiles']);
			$this->scriptVars=array_merge($this->scriptVars,$render_scriptAssets['scriptVars']);


			$filters_rendering_array['html']=$filters_html;
			$category_flt_tree_mode=$params->get('category_flt_tree_mode','0');



			/*
			 * Use event delegation
			 * only in non-ajax requests - otherwise these events will be assigned multiple times
			 */
			if(($this->results_trigger=='btn' || $this->results_loading_mode=='ajax') && ($jinput->get('view','')!='module' || $jinput->get('option','')!='com_customfilters')){
				$this->scriptProcesses[]="customFilters.assignEvents($module->id);";
				if($category_flt_tree_mode==false)$this->scriptProcesses[]="customFilters.addEventTree($module->id);";
			}else if(!($this->results_trigger=='btn' || $this->results_loading_mode=='ajax') && $category_flt_tree_mode==false)$this->scriptProcesses[]="customFilters.addEventTree($module->id);";



			//script/styles declarations
			if(!empty($this->stylesDeclaration))$filters_rendering_array['stylesDeclaration']=$this->stylesDeclaration;
			$filters_rendering_array['selected_flt']=$this->selected_flt;
			//only in non-ajax requests - otherwise will have the files into dom multiple times
			if(!empty($this->scriptFiles) && ($jinput->get('view','')!='module' && $jinput->get('option','')=='com_customfilters') || ($jinput->get('option','')!='com_customfilters')){
				$filters_rendering_array['scriptFiles']=$this->scriptFiles;
			}
			if(!empty($this->scriptVars))$filters_rendering_array['scriptVars']=$this->scriptVars;
			if(!empty($this->scriptProcesses))$filters_rendering_array['scriptProcesses']=$this->scriptProcesses;

			$filters_rendering_array['expanded_state']=$this->expanded;
			//reset tool
			if($display_reset_all && !empty($this->selected_flt)){
				$filters_rendering_array['resetUri']=$this->getResetUri();
			}
		}
		return $filters_rendering_array;
	}



	/**
	 * This function creates an assoc array with all the available filters
	 * The created array will have this form
	 * array('fltname1'=>array('disptype'=>string,'header'=>string,'smartSearch'=>boolean, options'=>array('0'=>array('label'=>string,'id'=>int,'enabled'=>int,'1'=>array('label'=>string,'id'=>int,...'n')))
	 *
	 * @param	string	The name of the variable which will be used in the filtering form
	 * @param	string	The header of the filter
	 * @param	string	Used only for custom filters.Indicates the type of the custom field
	 * @param	boolean	Indicates if a filter contains strings. In this case they should be encoded
	 * @author	Sakis Terz
	 * @since 	1.0
	 */
	public function setFilter($var_name,$header, $customfilter=null,$encoded_var=false){
		$activeOptions=array();
		$on_category_reset_others=false;
		$getActive=false;
		$is_customfield=strpos($var_name, 'custom_f_');
		$activeArray=array();
		$has_active_opt=false;
		$selected_array=array();
		$clear_opt=array();
		//add the counter settings
		if($is_customfield!==false)$field_key='custom_f';
		else $field_key=$var_name;
		$suffix=$this->fltSuffix[$field_key];
		$dependency_direction=$this->moduleparams->get('dependency_direction','all');

		$displayCounter=$this->moduleparams->get($suffix.'_display_counter_results','1');
		$display_empty_opt=$this->moduleparams->get($suffix.'_disable_empty_filters','1');


		$reset_type=$this->component_params->get('reset_results',0);
		if($dependency_direction=='t-b'){
			if(isset($this->selected_fl_per_flt[$var_name]))$selected_flt=$this->selected_fl_per_flt[$var_name];
			else $selected_flt=array();
		}
		else $selected_flt=$this->selected_flt;

		if($var_name=='virtuemart_category_id'){
			$category_flt_tree_mode=$this->moduleparams->get('category_flt_tree_mode','0');
			$cat_ordering=$this->moduleparams->get('categories_disp_order','tree');
			$on_category_reset_others=$this->moduleparams->get('category_flt_onchange_reset','filters');
		}

		$thereIsSelection=!empty($selected_flt);

		/*
		 * in case there is no selection
		 * or the only selection is the current filter
		 * or the display type is "all as enabled"
		 * or the dependency is top-to-bottom and its the 1st filter from top
		 * or reset filters on category change - for the categories filter
		 */
		if(!$thereIsSelection || ($thereIsSelection && isset($selected_flt[$var_name]) && count($selected_flt)==1) || $display_empty_opt=='2' || $on_category_reset_others=='filters_keywords' || ($on_category_reset_others=='filters' && empty($selected_flt['q']))){
			$results=$this->optHelper->getOptions($var_name,$customfilter);

			if($var_name=='virtuemart_category_id'){
				$options_ar=$results['options'];
			}else $options_ar=$results;

			/*
			 * In case of display type=(2)"all as enabled" and the displayCounter is true
			 * We should run the getActiveOptions to get the counter relative to the selected filters
			 * This should happen only if there are selections in other filters
			 */
			if($display_empty_opt=='2' && $options_ar && ($thereIsSelection && (empty($selected_flt[$var_name]) || count($selected_flt)>1)) && $displayCounter==true){
				$activeOptions=$this->optHelper->getActiveOptions($var_name,$customfilter);
				$getActive=true;
			}

		}
		//hide disabled
		else if($display_empty_opt=='0'){
			$options_ar=$this->optHelper->getActiveOptions($var_name,$customfilter,$joinFieldData=true);
			//when we have category tree we should get all the categories as the parent should be active when they have child
			if($var_name=='virtuemart_category_id' && $cat_ordering=='tree'){
				$results=$this->optHelper->getOptions($var_name,$customfilter);
				$maxLevel=$results['maxLevel'];
				if($maxLevel>0){
					$categories=$results['options'];
					$options_ar=$this->createTree($categories,$options_ar,$maxLevel);
				}
			}
		}
		//display disabled as disabled
		else if($display_empty_opt=='1'){
			$reults=$this->optHelper->getOptions($var_name,$customfilter);
			if($var_name=='virtuemart_category_id')$options_ar=$reults['options'];
			else $options_ar=$reults;

			if($options_ar)	{
				$activeOptions=$this->optHelper->getActiveOptions($var_name,$customfilter);
				$getActive=true;
			}
		}


		//give to each option the necessary properties
		if(is_array($options_ar) && count($options_ar)>0){
			$disp_type=$this->disp_types[$var_name];
			$displaySelectedOnTop=false;
			//display on top only for checkboxes , when they exceed a certain nr and the filter is not category
			if($var_name!='virtuemart_category_id' && $disp_type==3 && count($options_ar)>10)$displaySelectedOnTop=$this->moduleparams->get('disp_selected_ontop','1');
			$custom_flt_disp_empty=$this->moduleparams->get('custom_flt_disp_empty','0');
			$disp_clear_tool=$this->moduleparams->get('disp_clear','1');
			//get the active option of the filter
			//if the param is show as disabled
			//in every other case the $options_ar will contain the options that should be displayed
			//if($display_empty_opt=='1' && $thereIsSelection)$activeOptions=$this->optHelper->getActiveOptions($var_name);

			//when it returns true all are active
			if($activeOptions===true) {
				$activeOptions=array();
			}

			$act_opt_counter=count($activeOptions);
			$filters_opt[$var_name]=array();
			$filters_opt[$var_name]['var_name']=$var_name;
			$filters_opt[$var_name]['display']=$disp_type;
			$filters_opt[$var_name]['header']=$header;
			$filters_opt[$var_name]['smartSearch']=isset($this->smartSearch[$var_name])?$this->smartSearch[$var_name]:false;

			//display counter setting
			$filters_opt[$var_name]['dispCounter']=$displayCounter;

			$filters_opt[$var_name]['options']=array();
			//generate the 1st null option
			if($disp_type==1 || $disp_type==2 || $disp_type==4 || $disp_type==7 || (($disp_type==3 || $disp_type==10 || $disp_type==12) && $disp_clear_tool==1 && isset($selected_flt[$var_name]))){
				$filters_opt[$var_name]['options'][0]=array();
				$filters_opt[$var_name]['options'][0]['id']='';
				$filters_opt[$var_name]['options'][0]['active']=true;

				/*
				 * If the reset returns no products and is the only filter to be reset, then display the none as 1st option
				 * Otherwise display the Any as 1st option
				 */
				if($disp_type!=3 && $disp_type!=10 && $disp_type!=12){
					if($reset_type==0 && (count($selected_flt)==0 || (count($selected_flt)==1 && !empty($selected_flt[$var_name])))) $filters_opt[$var_name]['options'][0]['label']=JText::sprintf('MOD_CF_NONE',$header);
					else $filters_opt[$var_name]['options'][0]['label']=JText::sprintf('MOD_CF_ANY_HEADER',$header);
				}
				else {
					$filters_opt[$var_name]['options'][0]['label']=JText::_('MOD_CF_CLEAR');
				}

				$selected=0;
				$type="clear";
				//if no selection set as default
				if(!isset($selected_flt[$var_name]) || count($selected_flt[$var_name])==0){
					$selected=1;
				}
				$filters_opt[$var_name]['options'][0]['type']=$type;
				$filters_opt[$var_name]['options'][0]['selected']=$selected;
			}

			//store the inactive selected too
			$innactive_selected=array();


			$i=1;//there is also the 1st null option in some cases
			foreach ($options_ar as $key=>$opt){
				$filters_opt[$var_name]['options'][$key]=array();
				$filters_opt[$var_name]['options'][$key]['id']=$opt->id;
				$filters_opt[$var_name]['options'][$key]['label']=$opt->name;
				$filters_opt[$var_name]['options'][$key]['selected']=0;
				$filters_opt[$var_name]['options'][$key]['type']='option';
				if(!empty($opt->media_id))$filters_opt[$var_name]['options'][$key]['media_id']=$opt->media_id;

				//in case of categories we need some more properties
				if($var_name=='virtuemart_category_id' && $cat_ordering=='tree' && $disp_type!=1){
					if(isset($opt->level))$filters_opt[$var_name]['options'][$key]['level']=$opt->level;
					if(isset($opt->cat_tree))$filters_opt[$var_name]['options'][$key]['cat_tree']=$opt->cat_tree;
					if(isset($opt->isparent))$isparent=$opt->isparent;
					else $isparent=false;
					$filters_opt[$var_name]['options'][$key]['isparent']=$isparent;
					$filters_opt[$var_name]['options'][$key]['parent_id']=$opt->category_parent_id;
				}

				$select_opt=false;

				//check if selected
				if(isset($selected_flt[$var_name])){
					$opt_id=$opt->id;
					if(in_array($opt_id, $selected_flt[$var_name])) {
						$select_opt=true;
					}
				}

				//when there are active options , get the counter from the getActiveOptions function
				//this happens only when the display empty type is:"display as disabled" or "display as enabled" and there is a selection in another filter
				if($getActive){  //if($var_name=='virtuemart_category_id')echo $opt->id;
					if(isset($activeOptions[$opt->id]) || !empty($opt->isparent)){
						if($filters_opt[$var_name]['dispCounter'] && isset($activeOptions[$opt->id]->counter))$filters_opt[$var_name]['options'][$key]['counter']=$activeOptions[$opt->id]->counter;
						$filters_opt[$var_name]['options'][$key]['active']=true;
						$has_active_opt=true;
						$activeArray[]=$opt->id;

					}else{
						if($filters_opt[$var_name]['dispCounter'])$filters_opt[$var_name]['options'][$key]['counter']=0;
						$filters_opt[$var_name]['options'][$key]['active']=0;
						if($select_opt)$innactive_selected[]=$opt->id;
						//when all are enabled
						if($display_empty_opt=='2'){
							$filters_opt[$var_name]['options'][$key]['active']=1;
							if(isset($opt->counter) && $opt->counter>0)$has_active_opt=true;
							$activeArray[]=$opt->id;
						}
					}
				}else{
					if($filters_opt[$var_name]['dispCounter'] && isset($opt->counter)){
						$filters_opt[$var_name]['options'][$key]['counter']=$opt->counter;
						if((isset($opt->counter) && $opt->counter>0) || $display_empty_opt=='2'){
							$filters_opt[$var_name]['options'][$key]['active']=1;
							$activeArray[]=$opt->id;
							if(isset($opt->counter) && $opt->counter>0)$has_active_opt=true;
						}
						else {
							if(!empty($opt->isparent)){
								$filters_opt[$var_name]['options'][$key]['active']=1;
								unset($filters_opt[$var_name]['options'][$key]['counter']);
							}
							else $filters_opt[$var_name]['options'][$key]['active']=0;
							if($select_opt)$innactive_selected[]=$opt->id;
						}
					}
					//when there is no counter and there is no selection - all are active
					else{
						if(!empty($opt->emptyParent) && $disp_type==1)$filters_opt[$var_name]['options'][$key]['active']=false;
						else {
							$filters_opt[$var_name]['options'][$key]['active']=1;
							$activeArray[]=$opt->id;
							$has_active_opt=true;
						}
					}
				}

				if($select_opt){
					$filters_opt[$var_name]['options'][$key]['selected']=1;
					$opt=$filters_opt[$var_name]['options'][$key];


					if(isset($opt['cat_tree'])){
						$opt_tree=$opt['cat_tree'].'-'.$opt['id'];
						if(!in_array($opt_tree, $this->active_tree)){
							//used by the tree (categories), to indicate the selected category's tree
							$this->active_tree[]=$opt_tree;
						}
					}

					//if set selected on top unset it now and put later at the top
					if($displaySelectedOnTop){
						if(isset($filters_opt[$var_name]['options'][0])){
							$selected_array[0]=$filters_opt[$var_name]['options'][0];
							unset($filters_opt[$var_name]['options'][0]);
						}
						$selected_array[$opt['id']]=$opt;
						unset($filters_opt[$var_name]['options'][$key]);

					}
				}
				$i++;
			}
			cftools::setActiveTree($this->active_tree);
				
			/*
			 * if there are active subtrees, can be autoexpanded
			 * But can happen only:
			 * in categories
			 * When the there are active options
			 * When there is no category selected
			 * When the setting for auto-expand is active
			 * When the categories reset other filters. i.e. can be affected by the search
			 * When there is search 
			 */			
			if(
			$var_name=='virtuemart_category_id' && 
			!empty($activeArray) && 
			empty($this->active_tree) && 
			$this->moduleparams->get('categories_disp_order','tree')=='tree' && 
			$this->moduleparams->get('category_flt_auto_expand_subtrees','1') &&
			$this->moduleparams->get('category_flt_onchange_reset','filters')=='filters' && 
			!empty($selected_flt['q']))
			{
				$this->setActiveSubtrees($activeArray,$filters_opt[$var_name]['options']);
			}
				
			//there is a param for custom filters-to hide them if all are inactive
			if($is_customfield!==false && $custom_flt_disp_empty==false && (empty($activeArray) || $has_active_opt==false)){}
			else {
				//put selected on top
				$options=array_merge($selected_array,$filters_opt[$var_name]['options']);
				$filters_opt[$var_name]['options']=$options;
				$this->filters_opt[$var_name]=$filters_opt[$var_name];
				//check for inactive selected

				if(!empty($activeArray) && !empty($selected_flt[$var_name])){
					$innactive_selected=array_diff($selected_flt[$var_name],$activeArray);
				}
				if(count($innactive_selected)>0)$this->inactive_select_opt[$var_name]=$innactive_selected;
			}
		}
	}
	
	/**
	 * Set active subtrees
	 *
	 * @param 	array 	$activeArray	the active options
	 * @param 	array 	$options		all the options
	 * @since	2.2.1
	 */
	function setActiveSubtrees($activeArray,$options){ 
		//all are active		
		if(count($activeArray)==count($options)-1)return false;
		foreach($options as $opt){
			//if is active and not parent enable that subtree
			if($opt['active'] && !$opt['isparent']){
				if(isset($opt['cat_tree'])){
					$opt_tree=$opt['cat_tree'].'-'.$opt['id'];
					if(!in_array($opt_tree, $this->active_tree)){
						//used by the tree (categories), to indicate the selected category's tree
						$this->active_tree[]=$opt_tree;
					}
				}
			}
		}
		cftools::setActiveTree($this->active_tree);
	}

	/**
	 * It creates a tree (e.g. Categories), enabling also the parents of the active options
	 * This way the user can reach the active options in the tree depth
	 * @author	Sakis Terz
	 * @param  	All the options
	 * @param  	The active options
	 * @param  	The higher level
	 * @return	Array
	 * @since  	1.6.0
	 */
	function createTree($options, $activeOptions, $maxLevel){
		//if all are active it will be true
		if(!is_array($activeOptions))$activeOptions=array();
		$parent_categories=array();
		$parent_categories2=array();
		$activeKeys=array_keys($activeOptions);
		//find the parents of the active
		foreach($activeOptions as $aOpt){
			if($aOpt->category_parent_id>0){
				$parent_id=$aOpt->category_parent_id;
				$parent=$options[$parent_id];
				while($parent_id>0){
					if(!in_array($aOpt->category_parent_id, $activeKeys))$parent_categories[]=$parent_id;//stores the parents which are active
					$parent_categories2[]=$parent_id; //stores the parents of the active children
					$parent_id=$parent->category_parent_id;
					if($parent_id>0)$parent=$options[$parent_id];
				}

			}
		}

		foreach($options as $key=>&$opt){
			//unset those which are inactive or non parents of the active
			if(!in_array($opt->id, $activeKeys) && !in_array($opt->id, $parent_categories)){
				unset($options[$key]);
			}else {
				if(isset($activeOptions[$key]) && isset($activeOptions[$key]->counter))$opt->counter=$activeOptions[$key]->counter;
				//indicates that it is displayed only because its parent and is not included in the active options
				if(in_array($opt->id, $parent_categories) && !in_array($opt->id, $activeKeys))$opt->emptyParent=true;
				//find if a parent has any child
				if(!in_array($opt->id, $parent_categories2))unset($opt->isparent);
			}
		}unset($opt);
		//print_r($options);
		return $options;
	}




	/**
	 * Remove any inactive option from the selected options
	 * This array is used later by the getURI func which should not use the inactive to generate the option's URI
	 * @param	boolean	- indicates if the var that will be used is the per filter or not
	 * @author	Sakis Terz
	 * @since	1.0
	 */
	public function removeInactiveOpt(){
		if(empty($this->selected_flt))return $this->selected_flt;
		$myselection=$this->selected_flt;
		foreach ($myselection as $key=>&$array){
			if(!is_array($array))continue;
			foreach($array as $key2=>$sel){
				if(isset($this->inactive_select_opt[$key])){
					if(in_array($sel, $this->inactive_select_opt[$key]))unset($array[$key2]);
				}
			}
		}

		return $myselection;
	}

	/**
	 * Get an array with the filter headers
	 * used in the module's template
	 * @author	Sakis Terz
	 * @return	array	The header strings
	 * @since 	1.0
	 */
	public function getFltHeaders(){
		return $this->filters_headers_array;
	}



	/**
	 * creates the reset uri
	 * @author	Sakis Terz
	 * @since	1.5.0
	 * @return	string
	 */
	public function getResetUri(){
		$resetfields=$this->moduleparams->get('reset_all_reset_flt',array('virtuemart_manufacturer_id','price','custom_f'),'array');
		$itemId=$this->menu_params->get('cf_itemid','');
		$q_array=array();
		$q_array['option']= 'com_customfilters';
		$q_array['view']= 'products';
		if(!empty($itemId))$q_array['Itemid']=$itemId;

		foreach ($this->selected_flt as $key=>$selected){
			$new_key=strpos($key, 'custom_f_')!==false?'custom_f':$key;
			if(!in_array($new_key, $resetfields))$q_array[$key]=$selected;
		}
		$virtuemart_category_id='';
		/*
		 * if no category filter and category var. Or (category filter and category var and option=virtuemart)
		 * It means that we are in a category page and the category id should be kept
		 */
		if(isset($this->selected_flt['virtuemart_category_id']) && $this->moduleparams->get('category_flt_published',0)==false){
			$q_array['virtuemart_category_id']=$this->selected_flt['virtuemart_category_id'][0];
		}

		$u=JFactory::getURI();
		$query=$u->buildQuery($q_array);
		$uri='index.php?'.$query;
		return $uri;
	}
}