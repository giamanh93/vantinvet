<?php
/**
 *
 * Customfilters router
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: router.php 3 2012-2-1 14:05:00Z sakis $
 */

// no direct access
defined('_JEXEC') or die;
jimport( 'joomla.application.module.helper' );

function customfiltersBuildRoute(&$query){
	$CfRouterHelper=CfRouterHelper::getInstance();
	$siteLang=$CfRouterHelper->getLangPrefix();
	$segments=array();
	$db=JFactory::getDbo();

	//first get the filters
	if(!empty($query['virtuemart_category_id']) && is_array($query['virtuemart_category_id'])){
		$vm_categories=$query['virtuemart_category_id'];
		JArrayHelper::toInteger($vm_categories);
	}
	if(!empty($query['virtuemart_manufacturer_id']) && is_array($query['virtuemart_manufacturer_id'])){
		$vm_manufacturers=$query['virtuemart_manufacturer_id'];
		JArrayHelper::toInteger($vm_manufacturers);
	}
	//empty filters strings
	$no_category=urlencode(JText::_('CF_NO_VMCAT'));
	$no_manufacturer=urlencode(JText::_('CF_NO_VMMANUF'));
	$manuf_string='';
	$categ_string='';

	if(isset($query['view']) && $query['view']=='products')unset($query['view']);
	else return $segments; // do not build route for other views

	//categories
	if(!empty($vm_categories)){ //print_r($query['virtuemart_category_id']); echo '**';
		// Add the category alias
		$q = $db->getQuery(true);
		$q->select('slug');
		$q->from('#__virtuemart_categories_'.$siteLang);
		$q->where('virtuemart_category_id IN ('.implode(',',$vm_categories).')');
		$db->setQuery($q);
		$vm_cat_aliases=$db->loadColumn();
		if($vm_cat_aliases)	$categ_string=implode('__or__',$vm_cat_aliases);
		else $categ_string=$no_category;
		unset($query['virtuemart_category_id']);
	}else if(!empty($vm_manufacturers))$categ_string=$no_category;

	//manufacturers
	if(!empty($vm_manufacturers)){ 
		$vm_manufacturers=(array)$vm_manufacturers;		
		// Add the manuf alias
		$q = $db->getQuery(true);
		$q->select('slug');
		$q->from('#__virtuemart_manufacturers_'.$siteLang);
		$q->where('virtuemart_manufacturer_id IN ('.implode(',',$vm_manufacturers).')');
		$db->setQuery($q);
		$vm_mnf_aliases=$db->loadColumn();
		if($vm_mnf_aliases)	$manuf_string=implode('__or__',$vm_mnf_aliases);
		unset($query['virtuemart_manufacturer_id']);
	}//else if(existCustomfilter($query))$manuf_string=$no_manufacturer;

	$segments[]=$categ_string;
	$segments[]=$manuf_string;

	return $segments;
}

/**
 * @author	Sakis Terz
 * @since	1.0
 * @todo	Check if the segments param is sanitized
 */
function customfiltersParseRoute($segments){

	$CfRouterHelper=CfRouterHelper::getInstance();
	$siteLang=$CfRouterHelper->getLangPrefix();
	// Fix the segments
	$total = count($segments);
	for ($i=0; $i<$total; $i++)  {
		$segments[$i] = preg_replace('/:/', '-', $segments[$i], 1);
	}

	//empty filters strings
	$no_category=urlencode(JText::_('CF_NO_VMCAT'));
	$no_manufacturer=urlencode(JText::_('CF_NO_VMMANUF'));
	$db=JFactory::getDbo();

	//$vars['view']='products';

	$categories_ar=explode('__or__',$segments[0]);
	if(count($categories_ar)==1 && $categories_ar[0]==$no_category) {

	}else{
		//get the category ids
		$where_vmcat_slug=array();
		$vmcat_where_str='';
		//prepare the slugs for the query
		foreach ($categories_ar as $cat_slug){
			$where_vmcat_slug[]='slug='.$db->quote($db->escape($cat_slug));
		}
		$vmcat_where_str=implode(' OR ',$where_vmcat_slug);
			
		if($vmcat_where_str){
			// Add the manuf alias
			$q = $db->getQuery(true);
			$q->select('virtuemart_category_id');
			$q->from('#__virtuemart_categories_'.$siteLang);
			$q->where($vmcat_where_str);
			$db->setQuery($q);
			$vm_cat_ids=$db->loadColumn();
			$vars['virtuemart_category_id']=$vm_cat_ids;
		}
	}
	if(isset($segments[1])){
		$manuf_ar=explode('__or__',$segments[1]);
		if(count($manuf_ar)==1 && $manuf_ar[0]==$no_manufacturer) {
		}else{
			//get the manuf ids
			$where_vmmnf_slug=array();
			$vmmnf_where_str='';
			//prepare the slugs for the query
			foreach ($manuf_ar as $mnf_slug){
				$where_vmmnf_slug[]='slug='.$db->quote($db->escape($mnf_slug));

			}
			$vmmnf_where_str=implode(' OR ',$where_vmmnf_slug);

			if($vmmnf_where_str){
				// Add the manuf alias
				$q = $db->getQuery(true);
				$q->select('virtuemart_manufacturer_id');
				$q->from('#__virtuemart_manufacturers_'.$siteLang);
				$q->where($vmmnf_where_str);
				$db->setQuery($q);
				$vm_mnf_ids=$db->loadColumn();
				$vars['virtuemart_manufacturer_id']=$vm_mnf_ids;
			}
		}
	}
	//print_r($vars);
	return $vars;
}

/**
 * Check if any custom filter exist
 *
 * @param 	array $query
 * @since	1.9.0
 */
function existCustomfilter($query){
	foreach ($query as $key=>$q){
		if(strpos($key, 'custom_f_')!==false || strpos($key, 'price')!==false)return true;
	}
	return false;
}


Class CfRouterHelper{
	protected static $_cfrouter;
	protected $langPrefix='en_gb';
	
/**
 * Constructor function
 * since 1.9.0
 */
	function __construct(){
		if (!class_exists( 'VmConfig' )) {
			require(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		}
		VmConfig::loadConfig();
		$this->setLangPrefix();
	}

	/**
	 * Instantiation function
	 * @since 1.9.0
	 * 
	 */
	public static function getInstance(){
		if(empty(self::$_cfrouter)){
			self::$_cfrouter=new CfRouterHelper();
		}
		return self::$_cfrouter;
	}

	/**
	 * Function to set the current lang prefix
	 * When the parse root is called the constant VMLANG is undefined
	 * So we have to find the current language
	 *
	 * @since	1.5.0
	 * @version	1.9.0
	 */
	function setLangPrefix(){
		if(!defined('VMLANG')){
			jimport('joomla.language.helper');
			$languages = JLanguageHelper::getLanguages('lang_code');
			$jlang= JFactory::getLanguage();
			$siteLang=$jlang->getTag();
			//echo $siteLang;
			$langPrefix=strtolower(strtr($siteLang,'-','_'));
		}else $langPrefix=VMLANG;

		$this->langPrefix=$langPrefix;
	}

	/**
	 * Return the langprefix
	 *
	 * @since 1.9.0
	 */
	public function getLangPrefix(){
		return $this->langPrefix;
	}
}