<?php
// namespace components\com_jmap;
/**
 *
 * @package JMAP::components::com_jmap
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Polyfill abstract class for class based router on Joomla 3.3-
 *
 * @package JMAP::components::com_jmap
 * @since 2.3
 */
if (! class_exists ( 'JComponentRouterBase' )) {
	abstract class JComponentRouterBase {
		abstract public function build(&$query);
		abstract public function parse(&$segments);
	}
}

/**
 * Routing class from com_jmap
 *
 * @package JMAP::components::com_jmap
 * @since 2.3
 */
class JMapRouter extends JComponentRouterBase {
	/**
	 * Sitemap Joomla router, embeds little helper route
	 *
	 * @package JMAP::components::com_jmap
	 */
	function build(&$query) {
		$config = JFactory::getConfig ();
		static $appSuffix;
		if ($appSuffix) {
			$config->set ( 'sef_suffix', $appSuffix );
		}
		$componentParams = JComponentHelper::getParams ( 'com_jmap' );
		
		// Segments that will be translated and built for this URL, subtracted from $query that will be untranslated and not built
		$segments = array ();
		
		$app = JFactory::getApplication ();
		// Get all site menus
		$menus = $app->getMenu ( 'site' );
		
		// Mapping fallback for generic task name = view name if view is not used. View concept is only used wrong way by Joomla menus
		if (! isset ( $query ['view'] ) && isset ( $query ['task'] )) {
			if (strpos ( $query ['task'], '.' )) {
				list ( $controller_name, $controller_task ) = explode ( '.', $query ['task'] );
			}
			$mappedView = $controller_name;
		}
		
		// Helper Route here for existing menu item pointing to this $query, so try finding Itemid before all
		if (empty ( $query ['Itemid'] ) && $app->getClientId () != 1) {
			$component = JComponentHelper::getComponent ( 'com_jmap' );
			$menuItems = $menus->getItems ( 'component_id', $component->id );
			if (! empty ( $menuItems )) {
				// Route helper priority 1: view + dataset
				if (isset ( $query ['dataset'] )) {
					foreach ( $menuItems as $menuItem ) {
						if (isset ( $menuItem->query ) && isset ( $menuItem->query ['dataset'] )) {
							if ($menuItem->query ['dataset'] == $query ['dataset']) {
								// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
								$query ['Itemid'] = $menuItem->id;
								break;
							}
						}
					}
				}
				
				// Route helper priority 2: view only
				if (empty ( $query ['Itemid'] )) {
					foreach ( $menuItems as $menuItem ) {
						if (isset ( $menuItem->query ['dataset'] ) && is_numeric ( $menuItem->query ['dataset'] )) {
							continue;
						}
						if (isset ( $menuItem->query ) && isset ( $menuItem->query ['view'] )) {
							if (isset ( $query ['view'] ) && $menuItem->query ['view'] == $query ['view']) {
								// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
								$query ['Itemid'] = $menuItem->id;
								break;
							}
							
							if (isset ( $mappedView ) && $menuItem->query ['view'] == $mappedView) {
								// Found a link exact match to sitemap view default html format within a site menu, use the Itemid for alias: component/com_jmap=>alias
								$query ['Itemid'] = $menuItem->id;
								break;
							}
						}
					}
				}
			}
		}
		
		// Lookup for an menu itemid in $query, should be helped by route helper if any, for mod_menu links there is always $query = http://domain.com/?Itemid=123, and all is desetted by default
		if (empty ( $query ['Itemid'] )) {
			$menuItem = $menus->getActive ();
		} else {
			$menuItem = $menus->getItem ( $query ['Itemid'] );
		}
		// Store query info for menu, for example view name, for the menu selected fom Itemid or current as fallback
		$mView = (empty ( $menuItem->query ['view'] )) ? null : $menuItem->query ['view'];
		$mFormat = (empty ( $menuItem->query ['format'] )) ? 'html' : $menuItem->query ['format'];
		$mDataset = (empty ( $menuItem->query ['dataset'] )) ? null : $menuItem->query ['dataset'];
		// If this is a link to HTML sitemap format view assigned already to a menu, ensure to unset all by default to leave only menu alias
		if (isset ( $query ['view'] ) && ($mView == $query ['view']) && (! isset ( $query ['format'] ) || $mFormat == $query ['format']) && (! isset ( $query ['dataset'] ) || $mDataset == $query ['dataset'])) {
			unset ( $query ['view'] );
			unset ( $query ['format'] );
			unset ( $query ['dataset'] );
			// Return empty segments ONLY if link has a view specified that match a menu item. Controller.task is always left as a segment because could have specific behavior
			return $segments;
		}
		
		// Start desetting $query chunks assigning to segments
		// UNSET VIEW
		if (isset ( $query ['view'] )) {
			// Store view info for $query link
			$view = $query ['view'];
			// Assign and unset
			$segments [] = $query ['view'];
			unset ( $query ['view'] );
		}
		
		// UNSET TASK
		if (isset ( $query ['task'] )) {
			// Assign and unset
			$segments [] = str_replace ( '.', '-', $query ['task'] );
			unset ( $query ['task'] );
		}
		
		// UNSET FORMAT
		if (isset ( $query ['format'] )) {
			// Assign and unset
			$segments [] = $query ['format'];
			unset ( $query ['format'] );
			
			// Manage XML/NOT HTML JDocument format if detected
			$appSuffix = $config->get ( 'sef_suffix' );
			$config->set ( 'sef_suffix', false );
			
			// Manage suffix for backend routing executing site application
			if ($app->getClientId () == 1 && $componentParams->get ( 'sitemap_links_sef', false )) {
				$siteApplication = JApplication::getInstance ( 'site' );
				if (method_exists ( $siteApplication, 'set' )) {
					$siteApplication->set ( 'sef_suffix', false );
				}
			}
		}
		
		// UNSET XSLT
		$foundXslt = false;
		if (isset ( $query ['xslt'] )) {
			// Assign and unset
			$segments [] = $query ['xslt'] . '-formatted';
			unset ( $query ['xslt'] );
			$foundXslt = true;
		}
		
		// UNSET DATASET
		if (isset ( $query ['dataset'] )) {
			// Assign and unset
			if (! $foundXslt) {
				$segments [] = '0-formatted';
			}
			$segments [] = $query ['dataset'] . '-dataset';
			unset ( $query ['dataset'] );
		}
		
		// Finally return processed segments
		return $segments;
	}
	
	/**
	 * Parse the segments of a URL with following shapes:
	 *
	 * http://mydomain/component/jmap/view-task
	 *
	 * http://mydomain/component/jmap/viewname -> http://mydomain/component/jmap/viewname.display
	 * http://mydomain/component/jmap/controller-task
	 *
	 * component/jmap/ has to be handled through route helper for menu Itemid
	 * By convention view based Joomla components are overwritten by mapping viewname = taskname.display ex. view=sitemap is mapped to task=sitemap.display
	 * Even the task is considered and built/parsed fully as a dashed - entity like an article, a generic entity, etc: sitemap-display, sitemap-export AKA 1-myarticle
	 *
	 * @param
	 *        	array	The segments of the URL to parse.
	 * @return array URL attributes to be used by the application.
	 */
	function parse(&$segments) {
		$vars = array ();
		$count = count ( $segments );
		$app = JFactory::getApplication ();
		$componentParams = JComponentHelper::getParams ( 'com_jmap' );
		
		if ($count) {
			$count --;
			// VIEW-TASK is always 1° segment
			$segment = array_shift ( $segments );
			
			// Found a view/task
			if (version_compare ( JVERSION, '3.3', '>=' )) { // Fixed the semicolon/dash router issue?
				if (strpos ( $segment, '-' )) {
					$vars ['task'] = str_replace ( '-', '.', $segment );
				} else {
					$vars ['view'] = $segment;
				}
			} else { // Fallback on the compat mode 1:item
				if (strpos ( $segment, ':' )) {
					$vars ['task'] = str_replace ( ':', '.', $segment );
				} else {
					$vars ['view'] = $segment;
				}
			}
		}
		
		if ($count) {
			$count --;
			// FORMAT is always 2° segment
			$segment = array_shift ( $segments );
			if ($segment) {
				$vars ['format'] = $segment;
			}
		}
		
		if ($count) {
			$count --;
			// XSLT stylesheet is always 3° segment
			$segment = array_shift ( $segments );
			if (is_numeric ( ( int ) $segment )) {
				$vars ['xslt'] = ( int ) $segment;
			}
		}
		
		if ($count) {
			$count --;
			// Dataset is always 4° segment
			$segment = array_shift ( $segments );
			if (is_numeric ( ( int ) $segment )) {
				$vars ['dataset'] = ( int ) $segment;
			}
		}
		
		// Evaluate a forcing Itemid into vars and JMenu setter, link with no alias substitution, partially SEF from backend
		if (($ItemidByLink = $app->input->get ( 'Itemid', null )) && $componentParams->get ( 'sitemap_links_sef', false )) {
			$vars ['Itemid'] = $ItemidByLink;
			$menu = $app->getMenu ();
			$menu->setActive ( $vars ['Itemid'] );
		}
		
		return $vars;
	}
}

/**
 * JSitemap router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 */
function JMapBuildRoute(&$query) {
	$router = new JMapRouter ();
	
	return $router->build ( $query );
}
function JMapParseRoute($segments) {
	$router = new JMapRouter ();
	
	return $router->parse ( $segments );
}
