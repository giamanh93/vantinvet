<?php
// namespace administrator\components\com_jmap\framework\html;
/**  
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Menu Items element class
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class JMapHtmlMenu extends JObject {
	/**
	 * Tree recursion menu
	 * 
	 * @access private 
	 * @param int $id
	 * @param string $indent
	 * @param array $list
	 * @param array $children
	 * @param int $maxlevel
	 * @param int $level
	 * @param int $type
	 * @return array
	 */
	private static function treeRecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1) {
		if (@$children [$id] && $level <= $maxlevel) {
			foreach ( $children [$id] as $v ) {
				$id = $v->id;
	
				if ($type) {
					$pre = '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$pre = '- ';
					$spacer = '&nbsp;&nbsp;';
				}
	
				if ($v->parent == 0) {
					$txt = $v->name;
				} else {
					$txt = $pre . $v->name;
				}
				$pt = $v->parent;
				$list [$id] = $v;
				$list [$id]->treename = "$indent$txt";
				$list [$id]->children = count ( @$children [$id] );
				$list = self::TreeRecurse ( $id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type );
			}
		}
		return $list;
	}
	
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @param string $menuTitle
	 * @param boolean $withPriority
	 * @return array
	 */
	public static function getMenuItems($menuTitle = null, $withPriority = false) {
		static $list;
		
		if(is_null($list)) {
			$db = JFactory::getDbo();
			$titleMenuFilter = $menuTitle ? ' AND t.title = ' . $db->Quote($menuTitle) : null;
			// get a list of the menu items
			$query = "SELECT m.id, m.parent_id AS parent, m.title AS name, m.menutype, t.title, CONCAT((p.priority*100), '%') AS priority" .
					 "\n FROM #__menu AS m" .
					 "\n INNER JOIN #__menu_types AS t" .
					 "\n ON m.menutype = t.menutype" .
					 "\n LEFT JOIN #__jmap_menu_priorities AS p" .
					 "\n ON p.id = m.id" .
					 "\n WHERE m.published = 1" . $titleMenuFilter .
					 "\n AND m.client_id = 0" .
					 "\n ORDER BY m.menutype, m.parent_id, m.lft";
			$db->setQuery ( $query );
			$mitems = $db->loadObjectList ();
			$mitems_temp = $mitems;
			
			if(empty($mitems)) {
				return $mitems;
			}
			
			// establish the hierarchy of the menu
			$children = array ();
			// first pass - collect children
			foreach ( $mitems as $v ) {
				$id = $v->id;
				$pt = $v->parent;
				$list = @$children [$pt] ? $children [$pt] : array ();
				array_push ( $list, $v );
				$children [$pt] = $list;
			}
			// second pass - get an indent list of the items
			$list = self::treeRecurse ( intval ( $mitems [0]->parent ), '', array (), $children, 9999, 0, 0 );
		}
		
		$mitems = array ();
		$lastMenuType = null;
		$tmpMenuType = null;
		foreach ( $list as $list_a ) {
			if ($list_a->menutype != $lastMenuType) {
				if ($tmpMenuType) {
					$mitems [] = JHTML::_ ( 'select.option', '</OPTGROUP>' );
				}
				$mitems [] = JHTML::_ ( 'select.option', '<OPTGROUP>', $list_a->title );
				$lastMenuType = $list_a->menutype;
				$tmpMenuType = $list_a->menutype;
			}
			
			// Check for priority append
			$styles = null;
			if($withPriority && $list_a->priority) {
				$list_a->treename .=  ' - ' . $list_a->priority;
				$styles = 'class="haspriority"';
			}
			$mitems [] = JHTML::_ ( 'select.option', $list_a->id, $list_a->treename, array( 'option.attr'=>'style', 'attr'=>$styles));
		}
		if ($lastMenuType !== null) {
			$mitems [] = JHTML::_ ( 'select.option', '</OPTGROUP>' );
		}
		
		return $mitems;
	}
}