<?php
/**
 * @version		$Id: render.php 2013-1-31 12:45 sakis Terz $2
 * @package		customfilters
 * @subpackage	mod_cf_filtering
 * @copyright	Copyright (C) 2010 - 2014 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Class responsible for the rendering of the filters
 *
 * @package	customfilters
 * @author Sakis Terz
 * @since  1.9
 *
 */
class ModCfilteringRender{

	//contains the suffixes of the filters
	public $fltSuffix=array(
	'q'=>'keyword_flt',
	'virtuemart_category_id'=>'category_flt',
	'virtuemart_manufacturer_id'=>'manuf_flt',
	'price'=>'price_flt',
	'custom_f'=>'custom_flt');

	public $scriptProcesses=array();
	public $scriptFiles=array();
	public $scriptVars=array();

	public $direction='ltr';

	//contains all the filters with their options and their vars as set in the helper
	public $filters=array();

	//all the options in the selected filters
	public $selected_flt=array();

	//the selectied options substracting those which are inactive
	public $selected_flt_modif=array();

	//the selectied options used for each filter//used by top-to-bottom
	public $selected_fl_per_flt=array();

	/**
	 * Constructor of the render Class
	 *
	 * @param object $module
	 * @param array $selected_flt
	 * @param array $filters
	 */
	public function __construct($module,$selected_flt,$filters){
		$this->module=$module;
		$this->filters=$filters;
		$this->moduleparams=cftools::getModuleparams($module);
		$this->menu_params=cftools::getMenuparams();

		$this->selected_flt=$selected_flt['selected_flt'];
		$this->selected_flt_modif=$selected_flt['selected_flt_modif'];
		$this->selected_fl_per_flt=$selected_flt['selected_fl_per_flt'];
		//print_r($this->selected_flt);

		$doc= JFactory::getDocument();
		$this->direction=$doc->getDirection();

		$this->results_trigger=$this->moduleparams->get('results_trigger','sel');
		$this->results_loading_mode=$this->moduleparams->get('results_loading_mode','http');

		//get currency
		$japplication=JFactory::getApplication();
		$jinput=$japplication->input;
		$vendor_currency=cftools::getVendorCurrency();
		$virtuemart_currency_id=$jinput->get('virtuemart_currency_id',$vendor_currency['vendor_currency'],'int');
		$currency_id=$japplication->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',$virtuemart_currency_id);
		$this->currency_info=cftools::getCurrencyInfo($currency_id);
	}


	/**
	 * This function generates the html code for every filter
	 *
	 * @author	Sakis Terz
	 * @return	array	An array with the inside html code of every filter
	 * @since 	1.0
	 * @internal disp_types->1.select, 2.radio btns, 3.checkboxes, 4.Links, 5.Input text, 6.Slider
	 */
	public function renderFilters(){
		/* -Display Types-
		 * A display type can contain 2 or more display types (e.g. '1,3') in case we want to display the options with different ways within the same filter
		 * See Price Filter with input texts and Slider
		 */
		$active_tree=cftools::getActiveTree();
		$dateformats=cftools::getDateFormat();
		$dateFormat_php=$dateformats['dateFormat_php'];
		$dateFormat=$dateformats['dateFormat'];

		$nofollow=$this->moduleparams->get('indexfltrs_by_search_engines','0');
		$custom_flt_disp_empty=$this->moduleparams->get('custom_flt_disp_empty','0');
		$filters_html_array=array();
		$thereIsSelection=!empty($this->selected_flt);

		foreach($this->filters as $key=>$flt){
			
			$display_key=$key.'_'.$this->module->id;
			$is_customfield=strpos($key, 'custom_f_');
			//used to fetch params from specific filters
			if($is_customfield!==false)$ar_key='custom_f';
			else $ar_key=$key;
			$display_empty_opt=$this->moduleparams->get($this->fltSuffix[$ar_key].'_disable_empty_filters','1');

			$dispType=$flt['display'];
			$smartSearch=0;
			if(isset($flt['smartSearch']))$smartSearch=$flt['smartSearch'];
			if(isset($flt['dispCounter']))$dispCounter=$flt['dispCounter'];
			if(isset($flt['dispCounterDisabled']))$dispCounterDisabled=$flt['dispCounterDisabled'];
			$isexpanable_tree=0;
			if($key=='virtuemart_category_id'){
				$cat_ordering=$this->moduleparams->get('categories_disp_order','names');
				//theming
				$category_flt_tree_mode=$this->moduleparams->get('category_flt_tree_mode','0');
				$parent_link=$this->moduleparams->get('category_flt_parent_link','0');
				$current_tree_string=implode(',',$active_tree);
				if($current_tree_string)$current_tree_string.='-';//ending string
				if($cat_ordering=='tree' && $category_flt_tree_mode==0 && $parent_link==0)$isexpanable_tree=true;
			}

			//there is more than 1 display type for the filter using a comma as separator
			if(strpos($dispType, ',')>0){
				$disp_typeArray=explode(',',$dispType);
			}
			else $disp_typeArray=array($dispType);

			$html='';
			$options_ar=$flt['options'];
			$opt_found=false;

			if(count($options_ar)>0){
				foreach($disp_typeArray as $disp_type){
					$ul_class='';
					if($disp_type==9 || $disp_type==10)$ul_class=' cf_colorbtn_list';
					else if($disp_type==11 || $disp_type==12)$ul_class=' cf_btn_list';
					$innerHTML='';
					$innerHTML_clear='';
					$linkAttributes=array();

					//--select drop down--//
					if($disp_type==1){
						foreach($options_ar as $op){
							$opt=(object)$op;
							$js_trigger='';
							$select_flt='';
							$inactive='';
							if($opt->selected)$select_flt=' selected="selected"';
							if(!$opt->active){
								//if($display_empty_opt==1){
								$option_targ='1';//for inactive
								$inactive= 'disabled="disabled"';
								//}
								//else{$option_targ='';}
							}
							else{
								if($this->results_trigger=='btn' || $this->results_loading_mode=='ajax')$option_targ=$opt->id;
								else $option_targ=JRoute::_($this->getURL($flt,$opt->id));
								$opt_found=true;
							}

							$label=$opt->label;
							if($dispCounter && isset($opt->counter) && $opt->active){
								$label=$opt->label.'&nbsp;<span class="cf_flt_counter">('.$opt->counter.')</span>';
							}
							$innerHTML.='<option '.$inactive .'value="'.$option_targ.'" '.$select_flt.'>'.$label.'</option>';

						}
						//generate the HTML
						if($innerHTML){
							if($this->results_trigger!='btn' && $this->results_loading_mode!='ajax')$js_trigger='onchange="window.top.location.href=this.options[this.selectedIndex].value"';

							$html.='<select name="'.$key.'[]" class="cf_flt" '.$js_trigger.'>';
							$html.=$innerHTML;
							$html.='</select>';
						}
						unset($opt);
					}
					//-- 2.radios, 3.checkboxes, 4.links, 7.image links, 9.color buttons single, 10.color buttons multi, 11.button single, 12. button multi
					else if($disp_type==2 || $disp_type==4 || $disp_type==7){
						$innerHTML='';


						foreach($options_ar as $op){
							$script='';
							$opt=(object)$op;
							$select_flt='';
							$opt_class="cf_option";
							$li_class='';
							$class_name='';
							$style='';
							$color='';
							$inactive='';
							$element_id=$display_key.'_elid'.$opt->id;

							//create classes for the category tree
							if($key=='virtuemart_category_id' && $cat_ordering=='tree' && $opt->id>0 && isset($opt->cat_tree)){
								$li_class='cf_catOption';
								$li_class.=" cfLiLevel$opt->level";

								if($category_flt_tree_mode==false){//tree mode collapsed
									if(!empty($opt->isparent)){
										$opt_class.=' cf_parentOpt';
										$li_class.=' cf_parentLi';

										if(strpos($current_tree_string,$opt->cat_tree.'-'.$opt->id.'-')!==false)$opt_state=' cf_expand';
										else $opt_state=' cf_unexpand';
										$opt_class.=$opt_state;
									}
									else {
										$opt_class.=' cf_childOpt';

									}
									$opt_class.=' tree_'.$opt->cat_tree;
									$li_class.=' li-tree_'.$opt->cat_tree;

									if((empty($current_tree_string) && $opt->level>0) || (!empty($current_tree_string) && strpos($current_tree_string,$opt->cat_tree.'-')===false)){
										$li_class.=" cf_invisible";
									}
								}

							}

							if($opt->selected){
								$select_flt=' checked="checked"';
								$class_name=' cf_sel_opt';
							}
							if($opt->type=='clear')$class_name.=' cf_clear';
							else{
								//colors
								if($disp_type=='9' || $disp_type=='10'){
									$colors_multi=explode('|', $opt->label);
									$nr_colors_multi=count($colors_multi);
									$color_btn_width=100/$nr_colors_multi;
									//check to see if the value is color
									if($nr_colors_multi==1){
										$color=cftools::checkNFormatColor($opt->label);
										//if no color go to the next option
										if(empty($color))continue;
									}
									$opt_class.="  cf_color_btn";
								}
								//buttons
								else if($disp_type==11 || $disp_type==12)$opt_class.="  cf_button";
							}


							//generate the code for the active and inactive anchors
							if(!$opt->active){
								if($display_empty_opt==1){
									$opt_class.=" cf_disabled_opt";
									$inactive= 'disabled="disabled"';
									$style='';
									$anchor_label=$opt->label;

									//colors
									if($disp_type=='9' || $disp_type=='10' && $opt->type!='clear'){
										$clr_counter=0;
										$colors_html='';
										foreach($colors_multi as $clr){
											$color=cftools::checkNFormatColor($clr);
											if(empty($color))continue;
											$clr_counter++;
											$colors_html.='<span class="cf_color_inner" style="background-color:'.$color.'; width:'.$color_btn_width.'%;"></span>';
										}

										$option_anchor=$colors_html;

									}else $option_anchor='<span class="'.$opt_class.'" '.$style.'>'.$anchor_label.'</span>';
								}else{//hide if disabled
									$option_anchor='';
								}
							}
							else{
								$linkAttributes['class']="$opt_class$class_name";
								$linkAttributes['rel']='';
								if($this->moduleparams->get('indexfltrs_by_search_engines',0)==false || $opt->type=='clear')$linkAttributes['rel']="nofollow";
								//if we are using apply button for the inputs we should load only the option's id - urls won't used
								//For links and buttons the anchor should always created
								if(($this->results_trigger!='btn') || $disp_type=='4' || ($disp_type=='3' && $opt->type=='clear') || $disp_type=='9' || $disp_type=='10' || $disp_type=='11' || $disp_type=='12' ||!empty($opt->isparent)){
									$linkAttributes['data-module-id']=$this->module->id;
									$linkAttributes['id']=$element_id.'_a';
									$linkAttributes['style']=$style;

									$option_targ=JRoute::_($this->getURL($flt,$opt->id,$opt->type));
									if($this->results_trigger!='btn' && $this->results_loading_mode!='ajax')$script='onclick="window.top.location.href=\''.$option_targ.'\';"';
									$anchor_label=$opt->label;

									//colors
									if($disp_type=='9' || $disp_type=='10' && $opt->type!='clear'){
										$clr_counter=0;
										$colors_html='';
										foreach($colors_multi as $clr){
											$color=cftools::checkNFormatColor($clr);
											if(empty($color))continue;
											$clr_counter++;
											$colors_html.='<span class="cf_color_inner" style="background-color:'.$color.'; width:'.$color_btn_width.'%;"></span>';
										}

										$option_anchor=JHtml::link($option_targ,$colors_html,$linkAttributes);
									}else $option_anchor=JHtml::link($option_targ,$anchor_label,$linkAttributes);

								}else {
									$option_targ=$opt->id;
									$anchor_label=$opt->label;
									$option_anchor='<span class="'.$opt_class.'">'.$anchor_label.'</span>';
								}
								$opt_found=true;
							}

							if($option_anchor){
								$disp_input=true;

								//radios or checkboxes
								//in case of categories, parent categories with childs cannot be radios or checkboxes (only links)
								if(($disp_type==2 || $disp_type==3) && empty($opt->isparent)){
									$type_str='radio';
									

									//when checkboxes, hide the checkbox from the clear tool
									if($opt->type=='clear' && $type_str=='checkbox')$disp_input=false;
									$class_str='';
									if($li_class)$class_str=' class="'.$li_class.'"';
									$innerHTML.='<li '.$class_str.'>';
									if($disp_input){
										//$innerHTML.='<input '.$script.' type="'.$type_str.'" name="'.$key.'[]" '. $inactive .'class="cf_flt" id="'.$element_id.'" value="'.($opt->id).'" '.$select_flt.'/>';
										$innerHTML.='<label class="'.$class_name.'" for="'.$element_id.'">'.'<input '.$script.' type="'.$type_str.'" name="'.$key.'[]" '. $inactive .'class="cf_flt" id="'.$element_id.'" value="'.($opt->id).'" '.$select_flt.'/>'.$option_anchor.'</label>';
										if($dispCounter && isset($opt->counter) && $opt->active){
											$innerHTML.='<span class="cf_flt_counter">('.$opt->counter.')</span>';
										}
									}else{
										$innerHTML.='<span class="'.$class_name.'">'.$option_anchor.'</span>';
									}
									$innerHTML.='</li>';

								}

								//links (image,simple links), buttons, or clears
								else{
									$class_str='';
									if($opt->type=='clear')$li_class='cf_li_clear';
									if($li_class)$class_str=' class="'.$li_class.'"';
									$innerHTML.='<li '.$class_str.'>';

									//image links
									if($disp_type==7){
										if(empty($opt->media_id))$media_id=0;
										else $media_id=$opt->media_id;
										$img=cftools::getMediaFile($media_id);

										if(!empty($img) && $opt->type!='clear'){
											//inactive images
											if(!$opt->active){
												if($display_empty_opt==1){
													$opt_class.=" cf_disabled_opt_image";
													$img_option_anchor='<img src="'.$img->url.'" alt="'.$opt->label.'"/>';
													$img_option_anchor.='<span class="cf_img_caption">'.$opt->label;
													if($dispCounter && isset($opt->counter) && $opt->active){
														$img_option_anchor.='<span class="cf_flt_counter">('.$opt->counter.')</span>';
													}
													$img_option_anchor.='</span>';
												}else{//hide if disabled
													$img_option_anchor='';
												}
											}else{
												$opt_class.=" cf_opt_image";
												$imgWrapper='<img src="'.$img->url.'" alt="'.$opt->label.'"/>';
												$imgWrapper.='<span class="cf_img_caption">'.$opt->label;
												if($dispCounter && isset($opt->counter) && $opt->active){
													$imgWrapper.='<span class="cf_flt_counter">('.$opt->counter.')</span>';
												}
												$imgWrapper.='</span>';
												$linkAttributes['data-module-id']=$this->module->id;
												$linkAttributes['id']=$element_id.'_a';
												$img_option_anchor=JHtml::link($option_targ,$imgWrapper,$linkAttributes);
											}
											$innerHTML.='<div class="cf_img_wrapper '.$opt_class.'" style="width:'.$img->width.'px;">'.
											$img_option_anchor.'</div>';
										}
										else{//simple links
											$innerHTML.=$option_anchor;
											if($dispCounter && isset($opt->counter) && $opt->active){
												$innerHTML.='<span class="cf_flt_counter">('.$opt->counter.')</span>';
											}
										}
									}
									//color buttons, buttons
									else if($disp_type==9 || $disp_type==10 || $disp_type==11 || $disp_type==12){
										$innerHTML.=$option_anchor;
									}
									//simple links
									else {
										$innerHTML.=$option_anchor;
										if($dispCounter && isset($opt->counter) && $opt->active){
											$innerHTML.='<span class="cf_flt_counter">('.$opt->counter.')</span>';
										}
									}
									/* add a hidden input which holds the selected category.
									 * This way we can submit the form with a submit button too
									 */
									if($opt->selected){
										$innerHTML.='<input type="hidden" name="'.$key.'[]" value="'.($opt->id).'" />';
									}
									$innerHTML.='</li>';
								}
							}
						}//foreach(options)

						if($innerHTML){
							$list_id='cf_list_'.$key.'_'.$this->module->id;
							//smart search functionality
							//not images, color buttons
							if($smartSearch && $disp_type!=7 && $disp_type!=9 && $disp_type!=10){
								$smart_input_id='cf_smartSearch_'.$key.'_'.$this->module->id;
								$html.='<input type="text" class="cf_smart_search" id="'.$smart_input_id.'" placeholder="'.JText::_('MOD_CF_SEARCH').'"  maxlength="150"/>';
								$this->scriptProcesses[]="
								 var myFilter$key = new CfElementFilter('$smart_input_id', '#$list_id li',{								 
								  	module_id:{$this->module->id}, 
								  	isexpanable_tree:$isexpanable_tree,
								  	filter_key:'$key'								 	
								  });
								";
							}
							$html.='<ul class="cf_filters_list'.$ul_class.'" id="'.$list_id.'">';
							$html.=$innerHTML;
							$html.='</ul>';
						}
						unset($opt);
					}
				}
			}

		 $filters_html_array[$key.'_'.$this->module->id]=$html;
		}
		//echo base64_encode('sakis&makis'),base64_decode('2FraXMmbWFraXM=');
		return $filters_html_array;
	}


	/**
	 * Creates the href/URI for which each filter's option
	 * @param	the variable's name
	 * @param	the variable's value
	 * @author	Sakis Terz
	 * @return	String	The URI
	 * @since 	1.0
	 */
	private function getURL($filter,$var_value=NULL,$type='option'){
		$var_name=$filter['var_name'];
		$display_type=$filter['display'];
		$on_category_reset_others=false;
		

		if($var_name=='virtuemart_category_id'){
			$on_category_reset_others=$this->moduleparams->get('category_flt_onchange_reset','filters');
			if($on_category_reset_others){
				if(!empty($this->selected_flt_modif['virtuemart_category_id']))$categ_array=$this->selected_flt_modif['virtuemart_category_id'];
				else $categ_array=array();
			}
		}


		//in case of dependency top-bottom get the selected that this filter should use
		if($this->moduleparams->get('dependency_direction','t-b')=='t-b'){
			if(isset($this->selected_fl_per_flt[$var_name]))$q_array=$this->selected_fl_per_flt[$var_name];
			else $q_array=array();
		}
		//on category selection clear others
		else if($on_category_reset_others){
			$q_array['virtuemart_category_id']=$categ_array;
			if($on_category_reset_others=='filters')!empty($this->selected_flt['q'])?$q_array['q']=$this->selected_flt['q']:'';
		}
		else $q_array=$this->selected_flt_modif;		
		

		//in case of category tree, the parent options are always links, no matter what is the display type of the filter
		if(!empty($this->filters[$var_name]['options'][$var_value]->isparent))$display_type=4;
		//do not include also the parents in the urls of the child
		if(!empty($this->filters[$var_name]['options'][$var_value]['cat_tree'])){
			$parent_cat=explode('-',$this->filters[$var_name]['options'][$var_value]['cat_tree']);
			//print_r($q_array);
			foreach($parent_cat as $pcat){
				if(isset($q_array[$var_name])){
					$index=array_search($pcat,$q_array[$var_name]);
					if($index!==false){
						unset($q_array[$var_name][$index]);
					}
				}
			}
		}

		/*in case of select , radio or links (single select) remove previous selected criteria
		 *from the same filter as only 1 option from a filter should be selected
		 **/
		if(($display_type!=3 && $display_type!=10 && $display_type!=12) || $type=='clear') {unset($q_array[$var_name]);}

		/* in case an option is already selected
		 * The destination link of that option should omit it's value in case of checkboxes or multi-button
		 * to create the uncheck effect
		 */
		if(($display_type==3 || $display_type==10 || $display_type==12)&& (isset($q_array[$var_name]) && in_array($var_value, $q_array[$var_name]))){ 
			if(is_array($q_array[$var_name])) {
				$key=array_search($var_value,$q_array[$var_name]);
				unset($q_array[$var_name][$key]);
				$q_array[$var_name]=array_values($q_array[$var_name]); //reorder to fill null indexes
				if(count($q_array[$var_name])==0)unset($q_array[$var_name]); //if no any value unset it
			}
		}
		/*if not exist add it*/
		else if($var_value){
			if(isset($q_array[$var_name]) && is_array($q_array[$var_name])){

				//remove the null option which used only for sef reasons
				if(isset($q_array[$var_name][0])){
					if($q_array[$var_name][0]=='0' || $q_array[$var_name][0]==' '){$q_array[$var_name][0]=$var_value;	}
				}
				$q_array[$var_name][]=$var_value;
			}
			else $q_array[$var_name]=array($var_value);
		}
		
		$itemId=$this->menu_params->get('cf_itemid','');
		if($itemId)$q_array['Itemid']=$itemId;
		$q_array['option']='com_customfilters';
		$q_array['view']='products';

		//if trigger is on select load results
		//else load the module
		if($this->results_trigger=='btn') {
			unset($q_array['Itemid']);
			//$q_array['view']='module';
			$q_array['module_id']=$this->module->id;
			//$q_array['format']='raw';
		}

		$u=JFactory::getURI();
		$query=$u->buildQuery($q_array);
		$uri='index.php?'.$query;
		return $uri;
	}
	



	/**
	 * Return any existing script process
	 * @since	1.9.0
	 * @author	Sakis Terz
	 */
	public function getScriptAssets(){
		//$this->scriptFiles[]=JURI::root().'modules/mod_cf_filtering/assets/slider.js';
		//$this->scriptFiles[]=JURI::root().'modules/mod_cf_filtering/assets/drag_refactor.js';
		$scriptAssets['scriptProcesses']=$this->scriptProcesses;
		$scriptAssets['scriptFiles']=$this->scriptFiles;
		$scriptAssets['scriptVars']=$this->scriptVars;
		return $scriptAssets;
	}

}