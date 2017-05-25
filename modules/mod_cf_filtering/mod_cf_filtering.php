<?php
/**
 * @version		$Id: mod_cf_filtering.php 2013-04-30 12:16 sakis Terz $
 * @package		customfilters
 * @subpackage	mod_cf_filtering
 * @copyright	Copyright (C) 2008 - 2013 breakdesigns.net . All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customfilters'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'tools.php';
require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customfilters'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'input.php';
if (!class_exists( 'VmCompatibility' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customfilters'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmcompatibility.php');
//get the helper classes
require_once dirname(__FILE__).'/helper.php';
require_once dirname(__FILE__).'/optionsHelper.php';
require_once dirname(__FILE__).'/renderHelper.php';


//load the Virtuemart configuration
require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
VmConfig::loadConfig();

JText::script('MOD_CF_FILTERING_INVALID_CHARACTER');
JText::script('MOD_CF_FILTERING_PRICE_MIN_PRICE_CANNOT_EXCEED_MAX_PRICE');


$jlang= JFactory::getLanguage();
$jlang->load('com_customfilters');
$jlang->load('com_virtuemart');

//get the language code
if(!defined('VMLANG')){
	jimport('joomla.language.helper');
	$languages = JLanguageHelper::getLanguages('lang_code');
	$siteLang=$jlang->getTag();
	//echo $siteLang;
	$siteLang=strtolower(strtr($siteLang,'-','_'));
}else $siteLang=VMLANG;


if(!defined('JLANGPRFX')) define('JLANGPRFX', $siteLang);
$modObj=new ModCfFilteringHelper;
$filters_render_array=$modObj->getFilters($params,$module);
$filter_headers_array=$modObj->getFltHeaders();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

//calls the layout which will be used
//template overrides can be done
require(JModuleHelper::getLayoutPath('mod_cf_filtering',$params->get('layout', 'default')));