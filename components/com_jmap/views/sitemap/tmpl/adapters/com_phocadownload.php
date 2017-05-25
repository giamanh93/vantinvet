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

// Adapter for PhocaDownload items and categories route helper
$helperRouteClass= 'PhocaDownloadHelperRoute';
switch ($targetViewName) {
	case 'category':
		$classMethod = 'getFileRoute';
		$seflink = JRoute::_ ($helperRouteClass::$classMethod($elm->download, $elm->id, null, null, 0, 'download'));
		break;
}	

