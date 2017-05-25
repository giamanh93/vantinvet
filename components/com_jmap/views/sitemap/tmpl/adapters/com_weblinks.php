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

// Adapter for Weblinks items and categories route helper
$helperRouteClass= 'WeblinksHelperRoute';
switch ($targetViewName) {
	case 'weblink':
		$classMethod = 'getWeblinkRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $elm->language));
		break;
			
	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->language));
		break;
}	

