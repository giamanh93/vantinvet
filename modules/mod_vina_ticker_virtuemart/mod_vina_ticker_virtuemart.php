<?php
/*
# ------------------------------------------------------------------------
# Vina Product Ticker for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/* VirtueMart config */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

if(!class_exists('VmConfig')) {
	require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
}

$config = VmConfig::loadConfig();
VmConfig::loadJLang($module->module, true);

require_once dirname(__FILE__) . '/helper.php';

// Source setting
$moduleType 	= $params->get('moduleType', 'featured');
$filterCategory = (bool)$params->get('filterCategory', 0);
$categoryId 	= $params->get('categoryId', null);
$maxItems 		= $params->get('maxItems', 10);
$productName 	= (bool)$params->get('productName', 1);
$productImage 	= (bool)$params->get('productImage', 1);
$resizeImage 	= (bool)$params->get('resizeImage', 1);
$imageWidth 	= $params->get('imageWidth', 200);
$imageHeight 	= $params->get('imageHeight', 150);
$productRating 	= (bool)$params->get('productRating', 1);
$productStock 	= (bool)$params->get('productStock', 1);
$productDesc 	= (bool)$params->get('productDesc', 1);
$productPrice 	= (bool)$params->get('productPrice', 1);
$addtocart 		= (bool)$params->get('addtocart', 1);
$viewDetails 	= (bool)$params->get('viewDetails', 1);

$mainframe   = JFactory::getApplication();
$vCurrencyId = $mainframe->getUserStateFromRequest("virtuemart_currency_id", 'virtuemart_currency_id', vRequest::getInt('virtuemart_currency_id', 0));

$vendorId 		= vRequest::getInt('vendorid', 1);
$productModel 	= VmModel::getModel('Product');

$products = $productModel->getProductListing($moduleType, $maxItems, $productPrice, true, false, $filterCategory, $categoryId);
$productModel->addImages($products);

if(empty($products)) return false;
$currency = CurrencyDisplay::getInstance();

vmJsApi::jPrice();
vmJsApi::cssSite();

// Module Params
$moduleWidth	= $params->get('moduleWidth', '300px');
$moduleHeight	= $params->get('moduleHeight', 'auto');
$bgImage		 = $params->get('bgImage', NULL);
if($bgImage != '') {
	if(strpos($bgImage, 'http://') === FALSE) {
		$bgImage = JURI::base() . $bgImage;
	}
}
$isBgColor		= $params->get('isBgColor', 1);
$bgColor		= $params->get('bgColor', '#43609C');
$modulePadding	= $params->get('modulePadding', '10px');

$headerBlock	= $params->get('headerBlock', 1);
$headerText		= $params->get('headerText', '');
$headerColor	= $params->get('headerTextColor', '#FFFFFF');
$controlButtons	= $params->get('controlButtons', 1);

$isItemBgColor	= $params->get('isItemBgColor', 1);
$itemBgColor	= $params->get('itemBgColor', '#FFFFFF');
$itemPadding	= $params->get('itemPadding', '10px');
$itemTextColor	= $params->get('itemTextColor', '#141823');
$itemLinkColor	= $params->get('itemLinkColor', '#3B5998');

$direction		= $params->get('direction', 'up');
$easing			= $params->get('easing', 'jswing');
$speed			= $params->get('speed', 'slow');
$interval		= $params->get('interval', 5000);
$visible		= $params->get('visible', 2);
$mousePause		= $params->get('mousePause', 1);

$timthumb = 'modules/'.$module->module.'/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imageWidth.'&amp;h='.$imageHeight;
$timthumb = JURI::base() . $timthumb;

// include layout
require(JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default')));

// Display copyright text
modVinaTickerVirtueMartHelper::getCopyrightText($module);