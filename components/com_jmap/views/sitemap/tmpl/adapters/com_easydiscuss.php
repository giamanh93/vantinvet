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

// Adapter for Easydiscuss posts route helper
if(class_exists('EDR')) { // EasyDiscuss 4+
	$helperRouteClass= 'EDR';
} else {
	$helperRouteClass= 'DiscussRouter'; // EasyDiscuss < 4
}

// Use the component routing handler if it exists
$path = JPATH_SITE . '/components/com_easydiscuss/router.php';
// Use the custom routing handler if it exists
if (!isset($GLOBALS['jmapEDRouter']) && file_exists($path)) {
	require_once $path;
	$GLOBALS['jmapEDRouter'] = true;
}
$itemId = null;

if(!function_exists('jmapRecurseEDCategories')) {
	function jmapRecurseEDCategories($currentId, &$items) {
		static $loaded = array ();

		if (! isset ( $loaded [$currentId] )) {
				
			$db = DiscussHelper::getDBO ();
			$jDb = JFactory::getDbo();
				
			$query = 'SELECT '
					. $jDb->quoteName ( 'parent_id' ) .
					' FROM ' . $jDb->quoteName ( '#__discuss_category' ) .
					' WHERE ' . $jDb->quoteName ( 'id' ) . '=' . $db->Quote ( $currentId );
			$db->setQuery ( $query );
			$result = $db->loadResult ();
				
			$loaded [$currentId] = $result;
		}

		$result = $loaded [$currentId];

		if (! $result) {
			return;
		}

		$items [] = $result;

		if ($result != 0) {
			jmapRecurseEDCategories ( $result, $items );
		}
	}
}

switch ($targetViewName) {
	case 'post':
		$classMethod = 'getEntryRoute'; // Not used in this case
		if(isset($elm->jsitemap_category_id)) {
			// Buffered itemid already resolved for this category
			if(isset($GLOBALS['jmapEDStaticCatsBuffer'][$elm->jsitemap_category_id])) {
				$itemId = $GLOBALS['jmapEDStaticCatsBuffer'][$elm->jsitemap_category_id];
			}
			
			// Check if we have a category id to menu item
			if(!$itemId) {
				$db	= DiscussHelper::getDBO();
				$jDb = JFactory::getDbo();
				$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
						. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=categories&layout=listings&category_id='.$elm->jsitemap_category_id) 
						. ' OR ' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=forums&category_id='.$elm->jsitemap_category_id) . ') '
						. 'AND ' . $jDb->quoteName( 'published' ) . '=' . $db->Quote( '1' )
						. $helperRouteClass::getLanguageQuery()
						. ' LIMIT 1';
				$db->setQuery( $query );
				$itemId = $db->loadResult();
			}
			
			// Check if we have a parent category id to menu item
			if(!$itemId) {
				$parentCategories = array();
				jmapRecurseEDCategories($elm->jsitemap_category_id, $parentCategories);
				foreach ($parentCategories as $parentCat) {
					$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
							. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=categories&layout=listings&category_id='.$parentCat) 
							. ' OR ' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=forums&category_id='.$parentCat) . ') '
							. 'AND ' . $jDb->quoteName( 'published' ) . '=' . $db->Quote( '1' )
							. $helperRouteClass::getLanguageQuery()
							. ' LIMIT 1';
					$db->setQuery( $query );
					$itemId = $db->loadResult();
					if($itemId) {
						break;
					}
				}
			}
			if($itemId) {
				$GLOBALS['jmapEDStaticCatsBuffer'][$elm->jsitemap_category_id] = $itemId;
				$itemId = '&Itemid=' . $itemId;
			}
		}
		
		$seflink = JRoute::_ ('index.php?option=com_easydiscuss&view=post&id=' . $elm->id . $itemId);
		break;
		
	case 'categories':
		if(strpos($additionalQueryStringParams, 'layout=listings')) {
			$db	= DiscussHelper::getDBO();
			$jDb = JFactory::getDbo();
			$isED4 = $helperRouteClass == 'EDR' ? true : false;
			$classMethod = 'getItemIdByCategories';
			$itemId = $helperRouteClass::$classMethod($elm->category_id);
			
			// Fallback to the forums view route if ED4+
			if(!$itemId && $isED4) {
				$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
						. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=forums&category_id='.$elm->category_id) . ') '
						. 'AND ' . $jDb->quoteName( 'published' ) . '=' . $db->Quote( '1' )
						. $helperRouteClass::getLanguageQuery()
						. ' LIMIT 1';
				$db->setQuery( $query );
				$itemId = $db->loadResult();
			}
			
			// Category linked directly to a menu item
			if($itemId) {
				$itemId = '&Itemid=' . $itemId;
				$seflink = JRoute::_ ('index.php?option=com_easydiscuss' . $itemId);
			} else {
				// Check for parent categories
				$parentCategories = array();
				jmapRecurseEDCategories($elm->category_id, $parentCategories);
				foreach ($parentCategories as $parentCat) {
					$itemId = $helperRouteClass::$classMethod($parentCat);
					// Fallback to the forums view route if ED4+
					if(!$itemId && $isED4) {
						$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
								. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easydiscuss&view=forums&category_id='.$parentCat) . ') '
								. 'AND ' . $jDb->quoteName( 'published' ) . '=' . $db->Quote( '1' )
								. $helperRouteClass::getLanguageQuery()
								. ' LIMIT 1';
						$db->setQuery( $query );
						$itemId = $db->loadResult();
					}
					
					// Parent category linked directly to a menu item
					if($itemId) {
						$itemId = '&Itemid=' . $itemId;
						break;
					}
				}
				
				$categoryView = $isED4 ? 'forums' : 'categories';
				$layout = $isED4 ? '' : '&layout=listings';
				
				$seflink = JRoute::_ ('index.php?option=com_easydiscuss&view=' . $categoryView . $layout . '&category_id=' . $elm->category_id . $itemId);
			}
			
		}
		break;
		
	case 'tags':
		$seflink = JRoute::_ ('index.php?option=com_easydiscuss&view=tags&id=' . $elm->id . '&Itemid=' . $sefItemid);
		break;
	
	case 'badges':
		$seflink = JRoute::_ ('index.php?option=com_easydiscuss&view=badges&layout=listings&id=' . $elm->id . '&Itemid=' . $sefItemid);
		break;
}