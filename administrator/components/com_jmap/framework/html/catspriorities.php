<?php
// namespace administrator\components\com_jmap\framework\html;
/**
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Content categories for multiselect
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 * @since 3.0
 */
class JMapHtmlCatspriorities extends JObject {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 *
	 * @access public
	 * @return array
	 */
	public static function getCategories() {
		$categories = array ();
		$categories [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'COM_JMAP_NOCATS' ), 'value', 'text' );
		
		$db = JFactory::getDbo ();
		$query = $db->getQuery ( true )
				->select ( 'a.id, a.title, a.level, CONCAT((p.priority*100), "%") AS priority' )
				->from ( '#__categories AS a' )
				->join ( 'LEFT', '#__jmap_cats_priorities AS p ON a.id = p.id' )
				->where ( 'a.parent_id > 0' );
		
		// Filter on extension.
		$query->where ( 'a.extension = ' . $db->quote ( 'com_content' ) );
		$query->where ( 'a.published = 1' );
		$query->order ( 'a.lft' );
		
		$db->setQuery ( $query );
		$items = $db->loadObjectList ();
		
		foreach ( $items as &$item ) {
			$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
			$item->title = str_repeat ( '- ', $repeat ) . $item->title;
			
			// Check for priority append
			$styles = null;
			if($item->priority) {
				$item->title .=  ' - ' . $item->priority;
				$styles = 'class="haspriority"';
			}
			$categories [] = JHtml::_ ( 'select.option', $item->id, $item->title, array( 'option.attr'=>'style', 'attr'=>$styles) );
		}
		
		return $categories;
	}
}