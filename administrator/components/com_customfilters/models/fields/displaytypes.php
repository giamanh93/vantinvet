<?php
/**
 * @package customfilters
 * @version $Id: fields/displayTypes.php  2014-6-03 sakisTerzis $
 * @author Sakis Terzis (sakis@breakDesigns.net)
 * @copyright	Copyright (C) 2010-2014 breakDesigns.net. All rights reserved
 * @license	GNU/GPL v2
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 *
 * Class that generates a filter list
 * @author Sakis Terzis
 */
Class JFormFieldDisplaytypes extends JFormFieldList{

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$db=JFactory::getDbo();
		$q="SELECT id AS value, type AS text FROM #__cf_filtertypes ORDER BY id ASC";
		$db->setQuery($q);
		$options=$db->loadObjectList();
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}


}
