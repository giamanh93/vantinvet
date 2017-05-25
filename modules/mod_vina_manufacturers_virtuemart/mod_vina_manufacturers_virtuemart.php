<?php
/*
# ------------------------------------------------------------------------
# Vina Manufacturers Carousel for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

defined('_JEXEC') or die('Restricted access');

// VirtueMart config
if(!class_exists('VmConfig')) {
	require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
}

VmConfig::loadConfig();
VmConfig::loadJLang($module->module, true);

$model 			= VmModel::getModel('Manufacturer');
$manufacturers 	= $model->getManufacturers(true, true, true);
$model->addImages($manufacturers);
if(empty($manufacturers)) return false;

// get params
$showImage 		= $params->get('showImage', 1);
$showName		= $params->get('showName', 1);
$linkOnImage	= $params->get('linkOnImage', 0);
$linkOnName		= $params->get('linkOnName', 1);

$moduleWidth	= $params->get('moduleWidth', 	'100%');
$moduleHeight	= $params->get('moduleHeight', 	'auto');
$moduleMargin	= $params->get('moduleMargin', 	'0px');
$itemMargin		= $params->get('itemMargin', 	'0 5px');
$modulePadding	= $params->get('modulePadding', '30px 0 0');
$bgImage		= $params->get('bgImage', 		null);
if($bgImage != '') {
	if(strpos($bgImage, 'http://') === FALSE) {
		$bgImage = JURI::base() . $bgImage;
	}
}
$isBgColor		= $params->get('isBgColor', 	1);
$bgColor		= $params->get('bgColor', 		'#dddddd');
$captionBgColor	= $params->get('captionBgColor', '#ffd900');
$captionColor	= $params->get('captionColor', '#ffffff');
$pagination		= $params->get('pagination', 1);
$bgPagination	= $params->get('bgPagination', '#ffffff');
$textPagination	= $params->get('colorPagination', '#cccccc');
$bgPaginationA	= $params->get('bgPaginationActive', '#ffd900');
$textPaginationA = $params->get('colorPaginationActive', '#ffffff');
$navigation		= $params->get('navigation', 1);

$noItems		= $params->get('noItems', 'null');
$circular		= $params->get('circular', 1);
$infinite		= $params->get('infinite', 1);
$direction		= $params->get('direction', 'left');
$align			= $params->get('align', 'center');
$auto			= $params->get('auto', 1);
$mousewheel		= $params->get('mousewheel', 1);
$mouseSwipe		= $params->get('mouseSwipe', 1);
$touchSwipe		= $params->get('touchSwipe', 1);
$scrollItems	= $params->get('scrollItems', 'null');
$fx				= $params->get('fx', 'directscroll');
$easing			= $params->get('easing', 'linear');
$duration		= $params->get('duration', 500);
$pauseOnHover	= $params->get('pauseOnHover', 1);

require(JModuleHelper::getLayoutPath($module->module, 'default'));

// Display copyright text
echo '<div id="vina-copyright'.$module->id.'">© Free <a href="http://vinagecko.com/joomla-modules" title="Free Joomla! 3 Modules">Joomla! 3 Modules</a>- by <a href="http://vinagecko.com/" title="Beautiful Joomla! 3 Templates and Powerful Joomla! 3 Modules, Plugins.">VinaGecko.com</a></div>';