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

// Adapter for EventBooking items and categories route helper
include_once JPATH_SITE . '/components/com_eventbooking/helper/helper.php';
$helperRouteClass= 'EventbookingHelperRoute';
switch ($targetViewName) {
	case 'event':
		$classMethod = 'getEventRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid));
		break;
			
	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id));
		break;
}	

