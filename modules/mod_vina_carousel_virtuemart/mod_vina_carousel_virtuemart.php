<?php
/*
# ------------------------------------------------------------------------
# Vina Product Carousel for VirtueMart for Joomla 3
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
// module setting
$classSuffix 	= $params->get('moduleclass_sfx', '');
$moduleWidth	= $params->get('moduleWidth', 	'100%');
$moduleHeight	= $params->get('moduleHeight', 	'auto');
$moduleMargin	= $params->get('moduleMargin', 	'0px');
$modulePadding	= $params->get('modulePadding', '10px');
$bgImage		= $params->get('bgImage', 		null);
if($bgImage != '') {
	if(strpos($bgImage, 'http://') === FALSE) {
		$bgImage = JURI::base() . $bgImage;
	}
}
$isBgColor		= $params->get('isBgColor', 	1);
$bgColor		= $params->get('bgColor', 		'#CCCCCC');
$itemMargin		= $params->get('itemMargin', 	'0 5px');
$itemPadding	= $params->get('itemPadding', 	'10px');
$isItemBgColor	= $params->get('isItemBgColor', 1);
$itemBgColor	= $params->get('itemBgColor', 	'#FFFFFF');
$itemTextColor	= $params->get('itemTextColor', '#333333');
$itemLinkColor	= $params->get('itemLinkColor', '#0088CC');
// Carousel Params
$itemsVisible		= $params->get('items', 			4);
$itemsDesktop		= $params->get('itemsDesktop', 		'[1170,4]');
$itemsDesktopSmall	= $params->get('itemsDesktopSmall', '[980,3]');
$itemsTablet		= $params->get('itemsTablet', 		'[800,3]');
$itemsTabletSmall	= $params->get('itemsTabletSmall', 	'[650,2]');
$itemsMobile		= $params->get('itemsMobile', 		'[450,1]');
$singleItem			= $params->get('singleItem', 		0);
$itemsScaleUp		= $params->get('itemsScaleUp', 		0);
$slideSpeed			= $params->get('slideSpeed', 		200);
$paginationSpeed	= $params->get('paginationSpeed', 	800);
$rewindSpeed		= $params->get('rewindSpeed', 		1000);
$autoPlay			= $params->get('autoPlay', 			5000);
$stopOnHover		= $params->get('stopOnHover', 		1);
$navigation			= $params->get('navigation', 		0);
$rewindNav			= $params->get('rewindNav', 		1);
$scrollPerPage		= $params->get('scrollPerPage', 	0);
$pagination			= $params->get('pagination', 		1);
$paginationNumbers	= $params->get('paginationNumbers', 0);
$responsive			= $params->get('responsive', 		1);
$autoHeight			= $params->get('autoHeight', 		0);
$mouseDrag			= $params->get('mouseDrag', 		1);
$touchDrag			= $params->get('touchDrag', 		1);
// Timthumb Class Path
$timthumb = 'modules/'.$module->module.'/libs/timthumb.php?a=c&amp;q=99&amp;z=0&amp;w='.$imageWidth.'&amp;h='.$imageHeight;
$timthumb = JURI::base() . $timthumb;
// include layout
require(JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default')));

// Display copyright text
modVinaCarouselVirtueMartHelper::getCopyrightText($module);