<?php
// namespace administrator\components\com_jmap\framework\helpers;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Generic static helper class
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @since 3.5
 */
class JMapHelpersAssociations {
	/**
	 * Get the items associations
	 *
	 * @param   integer  $pk  Menu item id
	 * @usage JMapHelpersAssociations::getMenuAssociations($itemid)
	 *
	 * @return  array
	 */
	public static function getMenuAssociations($pk) {
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('m2.language, m2.id')
		->select($db->quoteName('lg.sef'))
		->from('#__menu as m')
		->join('INNER', '#__associations as a ON a.id=m.id AND a.context=' . $db->quote('com_menus.item'))
		->join('INNER', '#__associations as a2 ON a.key=a2.key')
		->join('INNER', '#__menu as m2 ON a2.id=m2.id')
		->join('INNER', $db->quoteName('#__languages', 'lg') . ' ON m2.language = lg.' . $db->quoteName('lang_code'))
		->where('m.id=' . (int) $pk)
		->where('m.type=' . $db->quote('component'));
		$db->setQuery($query);
	
		try {
			$menuitems = $db->loadObjectList ( 'sef' );
		} catch ( RuntimeException $e ) {
			return $associations;
		}
		
		foreach ( $menuitems as $tag => $item ) {
			$associations [$tag] = $item;
		}
		
		return $associations;
	}
	
	
	/**
	 * Get the associations.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tablename   The name of the table.
	 * @param   string   $context     The context
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
	 * @param   string   $catField    If the table has a catid field set it here. Null to not use it
	 * @usage JMapHelpersAssociations::getAssociations('com_content', '#__content', 'com_content.item', $articleid)
	 *
	 * @return  array                The associated items
	 */
	public static function getContentAssociations($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid') {
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select($db->quoteName('c2.language'))
		->select($db->quoteName('lg.sef'))
		->from($db->quoteName($tablename, 'c'))
		->join('INNER', $db->quoteName('#__associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
		->join('INNER', $db->quoteName('#__associations', 'a2') . ' ON a.key = a2.key')
		->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk))
		->join('INNER', $db->quoteName('#__languages', 'lg') . ' ON c2.language = lg.' . $db->quoteName('lang_code'));
		
		// Use alias field ?
		if (! empty ( $aliasField )) {
			$query->select ( $query->concatenate ( array (
					$db->quoteName ( 'c2.' . $pk ),
					$db->quoteName ( 'c2.' . $aliasField ) 
			), ':' ) . ' AS ' . $db->quoteName ( $pk ) );
		} else {
			$query->select ( $db->quoteName ( 'c2.' . $pk ) );
		}
	
		// Use catid field ?
		if (!empty($catField)) {
			$query->join(
					'INNER',
					$db->quoteName('#__categories', 'ca') . ' ON ' . $db->quoteName('c2.' . $catField) . ' = ca.id AND ca.extension = ' . $db->quote($extension)
			)
			->select(
					$query->concatenate(
							array('ca.id', 'ca.alias'),
							':'
					) . ' AS ' . $db->quoteName($catField)
			);
		}
	
		$query->where('c.' . $pk . ' = ' . (int) $id);
	
		$db->setQuery($query);
	
		try {
			$items = $db->loadObjectList('sef');
		}
		catch (RuntimeException $e) {
			return $associations;
		}
	
		if ($items) {
			foreach ($items as $tag => $item) {
				$associations[$tag] = $item;
			}
		}
	
		return $associations;
	}
}
