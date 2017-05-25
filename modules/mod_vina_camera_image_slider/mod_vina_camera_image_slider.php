<?php
/*
# ------------------------------------------------------------------------
# Vina Camera Image Slider for Joomla 3
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
require_once dirname(__FILE__) . '/helper.php';

// load json code
$slider = json_decode($params->get('slides', ''));

// check if don't have any slide
if(!$slider) {
	echo "You don't have any slide!";
	return;
}

// load data
$slides = modVinaCameraImageSliderHelper::getSildes($slider);

// get params
$moduleWidth		= $params->get('moduleWidth', '100%');
$maxWidth			= $params->get('maxWidth', '100%');
$moduleHeight		= $params->get('moduleHeight', '50%');
$moduleStyle		= $params->get('moduleStyle', 'camera_black_skin');
$resizeImage		= $params->get('resizeImage', 1);
$imageWidth			= $params->get('imageWidth', '600');
$imageHeight		= $params->get('imageHeight', '300');
$displayCaptions	= $params->get('displayCaptions', 1);
$loaderStyle		= $params->get('loaderStyle', 'pie');
$piePosition		= $params->get('piePosition', 'rightTop');
$barPosition		= $params->get('barPosition', 'bottom');
$barDirection		= $params->get('barDirection', 'leftToRight');
$fx					= $params->get('fx', 'random');
$pauseHover			= $params->get('pauseHover', 1);
$pauseOnClick		= $params->get('pauseOnClick', 1);
$navigation			= $params->get('navigation', 1);
$navigationHover	= $params->get('navigationHover', 0);
$playPause			= $params->get('playPause', 1);
$pagination			= $params->get('pagination', 0);
$thumbnails			= $params->get('thumbnails', 1);
$thumbnailWidth		= $params->get('thumbnailWidth', '100');
$thumbnailHeight	= $params->get('thumbnailHeight', '75');
$duration			= $params->get('duration', 7000);
$transPeriod		= $params->get('transPeriod', 1500);

// display layout
require JModuleHelper::getLayoutPath('mod_vina_camera_image_slider', $params->get('layout', 'default'));