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
 * Articles multiselect element class
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class JMapHtmlArticles extends JObject {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @return array
	 */
	public static function getArticles() {
		$db = JFactory::getDBO ();
		
		$articleOptions = array();
		$articleOptions[] = JHtml::_('select.option', '0', JText::_('COM_JMAP_NOARTICLES'), 'value', 'text');
		$categories = array();
		$categories = JHtml::_('category.options', 'com_content');
		
				
		if(!empty($categories)) {
			foreach ($categories as $category) {
				if(!$category->value) {
					continue;
				}
				// Get category indent from cat to replicate on articles
				preg_match('/^([-\s])+/', $category->text, $matches);
				$indent = null;
				if(isset($matches[0])) {
					$indent = $matches[0];
				}
				
				// Get a list of articles in this category
				$query = "SELECT c.id AS value, c.title AS text" .
						 "\n FROM " . $db->quoteName('#__content') . " AS " . $db->quoteName('c') .
						 "\n WHERE c.state = 1" .
						 "\n AND c.catid = " . (int)$category->value .
						 "\n ORDER BY c.ordering";
				$db->setQuery ( $query );
				$articles = $db->loadObjectList ();
				
				// Group articles by OPTGROUP category
				$articleOptions[] = JHTML::_ ( 'select.option', '<OPTGROUP>', $category->text );
				
				if(!empty($articles)) {
					foreach ($articles as $article) {
						$articleOptions[] = JHtml::_('select.option', $article->value, $indent . $article->text, 'value', 'text');
					}
				}
				
				// Close the OPTGROUP
				$articleOptions[] = JHTML::_ ( 'select.option', '</OPTGROUP>');
			}
		}
		
		return $articleOptions;
	}
}