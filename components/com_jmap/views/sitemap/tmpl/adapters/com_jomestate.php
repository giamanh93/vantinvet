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

// Adapter for JomEstate properties route helper
$helperRouteClass= 'JomestateHelperRoute';
switch ($targetViewName) {
	case 'item':
		$classMethod = 'getArticleRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->id, $elm->alias, $elm->categories_id, null));
		break;
}	

