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
 * Datasets available
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class JFormFieldDatasets extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Datasets';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$db = JFactory::getDBO ();
		$dataSets = array ();
		
		// get a list of the menu items
		$query = "SELECT dset.id AS value, dset.name AS text" .
				 "\n FROM #__jmap_datasets AS dset" .
				 "\n WHERE dset.published = 1" .
				 "\n ORDER BY dset.name";
		$db->setQuery ( $query );
		$dataSets = $db->loadObjectList ();
		
		array_unshift($dataSets, JHtml::_('select.option', null, JText::_('COM_JMAP_NODATASET_FILTER')));
		
		return JHtml::_('select.genericlist', $dataSets, $this->name, 'size="20" style="width: 250px"', 'value', 'text', $this->value);
	} 
}
