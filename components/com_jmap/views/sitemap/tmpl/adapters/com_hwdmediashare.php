<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage adapters
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

// Adapter for HwdMediaShare media items
$helperRouteClass= 'hwdMediaShareHelperRoute';
switch ($targetViewName) {
	case 'mediaitem':
		$classMethod = 'getMediaItemRoute';
		$seflink = @JRoute::_ ($helperRouteClass::$classMethod($elm->id));
		break;
}	

