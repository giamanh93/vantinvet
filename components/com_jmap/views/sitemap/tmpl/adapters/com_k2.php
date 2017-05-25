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

// Adapter for K2 items and categories route helper
if (!defined('K2_JVERSION')) {
	define('K2_JVERSION', JVERSION);
}
$helperRouteClass= 'K2HelperRoute';
switch ($targetViewName) {
	case 'item':
		$classMethod = 'getItemRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid));
		break;
			
	case 'itemlist':
		if(!strpos($additionalQueryStringParams, 'tag')) {
			$classMethod = 'getCategoryRoute';
			$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id));
			break;
		}
		
		if(strpos($additionalQueryStringParams, 'tag')) {
			$classMethod = 'getTagRoute';
			$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->tag));
			break;
		}
}	

