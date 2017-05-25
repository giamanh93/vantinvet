<?php
/*
# ------------------------------------------------------------------------
# Module: Vina Treeview for VirtueMart
# ------------------------------------------------------------------------
# Copyright (C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://VinaGecko.com
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
$useCache		= $params->get('useCache', 0) ? true : false;
$categories 	= $categoryModel->getChildCategoryList($vendorId, $categoryID, $fieldSort, $ordering, $useCache);

require(JModuleHelper::getLayoutPath($module->module, 'default'));

// Display copyright text
modVinaTreeViewVMartHelper::getCopyrightText($module);