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
 * Content categories for multiselect
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class JMapHtmlCategories extends JObject {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @return array
	 */
	public static function getCategories() {
		$categories = array();
		$categories[] = JHtml::_('select.option', '0', JText::_('COM_JMAP_NOCATS'), 'value', 'text');
		$categories = array_merge($categories, JHtml::_('category.options', 'com_content'));
		
		return $categories;
	}
}