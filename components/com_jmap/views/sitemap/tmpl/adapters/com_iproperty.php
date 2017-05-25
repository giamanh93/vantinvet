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
$helperRouteClass= 'IpropertyHelperRoute';
switch ($targetViewName) {
	case 'property':
		$classMethod = 'getPropertyRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->cat_id));
		break;
			
	case 'cat':
		$classMethod = 'getCatRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id));
		break;
}	

