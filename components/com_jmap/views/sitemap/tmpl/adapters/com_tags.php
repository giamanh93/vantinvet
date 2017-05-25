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

// Adapter for Newsfeeds items and categories route helper
$helperRouteClass= 'TagsHelperRoute';
switch ($targetViewName) {
	case 'tag':
		$classMethod = 'getTagRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id));
		break;
}	

