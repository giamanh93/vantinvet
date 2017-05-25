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

// Adapter for DMS items and categories route helper
$helperRouteClass= 'DMSHelperRoute';
switch ($targetViewName) {
	case 'document':
		$classMethod = 'getDocumentRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid));
		// Not resolved to a category menu?
		if(strpos($seflink, 'component/dms')) {
			$needles = array('categories'=>array(0));
			$fallbackItemid = $helperRouteClass::findItem($needles);
			$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->catid, $fallbackItemid));
		}
		
		break;
			
	case 'category':
		$classMethod = 'getCategoryRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id));
		// Not resolved to a category menu?
		if(strpos($seflink, 'component/dms')) {
			$needles = array('categories'=>array(0));
			$fallbackItemid = $helperRouteClass::findItem($needles);
			$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $fallbackItemid));
		}
		break;
}	

