<?php
/*
# ------------------------------------------------------------------------
# Vina Articles Carousel for Joomla 3
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
require_once dirname(__FILE__) . '/helper.php';

$input 		= JFactory::getApplication()->input;
$idbase 	= $params->get('catid');
$cacheid 	= md5(serialize(array ($idbase, $module->module)));

$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class 	= 'ModVinaArticlesCarouselHelper';
$cacheparams->method 	= 'getList';
$cacheparams->methodparams 	= $params;
$cacheparams->modeparams 	= $cacheid;

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if(empty($list))
{
	echo 'No item found! Please check your config!';
	return;
}

// get params
$moduleclass_sfx 	= $params->get('moduleclass_sfx', '');
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

// display params
$showImage			= $params->get('showImage', 1);
$resizeImage		= $params->get('resizeImage', 1);
$imagegWidth		= $params->get('imagegWidth', 100);
$imagegHeight		= $params->get('imagegHeight', 100);
$showTitle			= $params->get('showTitle', 1);
$showCreatedDate	= $params->get('show_date', 0);
$showCategory		= $params->get('show_category', 0);
$showHits			= $params->get('show_hits', 0);
$introText			= $params->get('show_introtext', 1);
$readmore			= $params->get('show_readmore', 1);

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

$thumb = JURI::base() . 'modules/mod_vina_carousel_content/libs/timthumb.php?a=c&q=99&z=0&w='.$imagegWidth.'&h='.$imagegHeight;

// include layout
require JModuleHelper::getLayoutPath('mod_vina_carousel_content', $params->get('layout', 'default'));

// Display copyright text
ModVinaArticlesCarouselHelper::getCopyrightText($module);