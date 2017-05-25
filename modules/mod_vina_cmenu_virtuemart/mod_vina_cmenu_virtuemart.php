<?php
/*
# ------------------------------------------------------------------------
# Vina Category Menu for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// VirtueMart Setting
if(!class_exists('VmConfig')) {
	require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
}

VmConfig::loadConfig();
VmConfig::loadJLang($module->module, true);

require_once(dirname(__FILE__).'/helper.php');

$categoryModel 	= VmModel::getModel('Category');
$categoryID 	= $params->get('parentCategoryID', 0);
$vendorId		= 1;
$fieldSort		= $params->get('sort', 'category_name');
$ordering		= $params->get('ordering', 'asc');
$count			= $params->get('showCountItems', 1);
$showHomeMenu	= $params->get('showHomeMenu', 1);
$menuItemId		= $params->get('menuItemId', null);

$bgColor		= $params->get('bgColor', '#2b2f3a');
$mainWidth		= $params->get('mainWidth', 'auto');
$mainAlign		= $params->get('mainAlign', 'align-left');
$mainFontSize	= $params->get('mainFontSize', '14px');
$mainBackground	= $params->get('mainBackground', '#333333');
$mainTextColor	= $params->get('mainTextColor', '#7a8189');
$mainTextHover	= $params->get('mainTextHover', '#ffffff');

$subWidth			= $params->get('subWidth', '130px');
$subFontSize		= $params->get('subFontSize', '12px');
$subTextColor		= $params->get('subTextColor', '#9ea2a5');
$subTextHover		= $params->get('subTextHover', '#8c9195');
$subBackground		= $params->get('subBackground', '#ffffff');
$subBackgroundHover	= $params->get('subBackgroundHover', '#fba026');
$subBorder			= $params->get('subBorder', '#eeeeee');

$useCache	= $params->get('useCache', 0) ? true : false;
$categories = $categoryModel->getChildCategoryList($vendorId, $categoryID, $fieldSort, $ordering, $useCache);

require(JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default')));

// Display copyright text
modVinaCMenuVMartHelper::getCopyrightText($module);