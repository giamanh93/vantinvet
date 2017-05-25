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

// Adapter for Easyblog posts route helper
$helperRouteClass= 'EasyBlogRouter';
switch ($targetViewName) {
	case 'entry':
		// Set empty in all cases
		$itemId = null;
		
		// Use the component routing handler if it exists
		$path = JPATH_SITE . '/components/com_easyblog/router.php';
		// Use the custom routing handler if it exists
		if (!isset($GLOBALS['jmapEBRouter']) && file_exists($path)) {
			require_once $path;
			$GLOBALS['jmapEBRouter'] = true;
		}
		
		// Easyblog < 5
		if(isset($elm->category_id)) {
			// Buffered itemid already resolved for this category
			if(isset($GLOBALS['jmapEBStaticCatsBuffer'][$elm->category_id])) {
				$itemId = $GLOBALS['jmapEBStaticCatsBuffer'][$elm->category_id];
			}
			
			// Check if we have a category id to menu item
			if(!$itemId) {
				$db	= EasyBlogHelper::db();
				$jDb = JFactory::getDbo();
				$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
						. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easyblog&view=categories&layout=listings&id='.$elm->category_id) . ') '
						. 'AND ' . $jDb->quoteName( 'published' ) . '=' . $db->Quote( '1' )
						. $helperRouteClass::getLanguageQuery()
						. ' LIMIT 1';
				$db->setQuery( $query );
				$itemId = $db->loadResult();
			}
			
			// Check if we have a parent category id to menu item
			if(!$itemId) {
				$parentCategories = array();
				$helperRouteClass::getCategoryParentId($elm->category_id, $parentCategories);
				foreach ($parentCategories as $parentCat) {
					$query	= 'SELECT ' . $jDb->quoteName('id') . ' FROM ' . $jDb->quoteName( '#__menu' ) . ' '
							. 'WHERE (' . $jDb->quoteName( 'link' ) . '=' . $db->Quote( 'index.php?option=com_easyblog&view=categories&layout=listings&id='.$parentCat) . ') '
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
				$GLOBALS['jmapEBStaticCatsBuffer'][$elm->category_id] = $itemId;
				$itemId = '&Itemid=' . $itemId;
			}
		} elseif (isset ( $elm->jsitemap_category_id )) { // Easyblog >= 5
			// Buffered itemid already resolved for this category
			if(isset($GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id])) {
				$itemId = $GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id];
			}
			
			// Check always if we have a blogger author information and give priority to the author menu item
			if (isset($elm->created_by)) {
				$helperRouteClass = 'EBR';
				$classMethod = 'getItemIdByBlogger';
				$itemId = $helperRouteClass::$classMethod ( $elm->created_by );
			}
			
			// Check if we have a direct category id to menu item
			if (! $itemId) {
				$helperRouteClass = 'EBR';
				$classMethod = 'getItemIdByCategories';
				$itemId = $helperRouteClass::$classMethod ( $elm->jsitemap_category_id );
			}
			
			// Check if we have a parent category id to menu item
			if (! $itemId) {
				$db	= EasyBlogHelper::db();
				$jDb = JFactory::getDbo();
				$parentCategories = array();
				$query	= 'SELECT parent.id' . 
						  ' FROM ' . $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('node') . ', ' .
						  $jDb->quoteName( '#__easyblog_category' ) . ' AS ' . $jDb->quoteName('parent') . 
						  ' WHERE node.lft BETWEEN parent.lft AND parent.rgt' .
						  ' AND node.id = ' . $db->Quote( $elm->jsitemap_category_id ) .
						  ' AND parent.id != ' . $db->Quote( $elm->jsitemap_category_id ) .
						  ' ORDER BY parent.lft';
				$db->setQuery( $query );
				$parentCategories = $db->loadResultArray();
				
				// Found parent categories?
				if(!empty($parentCategories)) {
					foreach ($parentCategories as $parentCat) {
						$itemId = $helperRouteClass::$classMethod ( $parentCat );
						if($itemId) {
							break;
						}
					}
				}
			}
			
			if ($itemId) {
				$GLOBALS['jmapEBStaticCatsBuffer'][$elm->jsitemap_category_id] = $itemId;
				$itemId = '&Itemid=' . $itemId;
			}
		}
		
		// Final SEF link routing
		$seflink = JRoute::_ ('index.php?option=com_easyblog&view=entry&id=' . $elm->id . $itemId);
		break;
}	

