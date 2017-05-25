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
 * Data sources available
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class JFormFieldDataSources extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'DataSources';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$db = JFactory::getDBO ();
		$dataSources = array ();
		$dataSourcesOptions = array();
		
		// get a list of the menu items
		$query = "SELECT dsource.id, dsource.name, dsource.type" .
				 "\n FROM #__jmap AS dsource" .
				 "\n WHERE dsource.published = 1" .
				 "\n ORDER BY dsource.type, dsource.ordering";
		$db->setQuery ( $query );
		$dataSources = $db->loadObjectList ();
		
		$lastDSType = null;
		$tmpDSType = null;
		foreach ( $dataSources as $dataSource ) {
			if ($dataSource->type != $lastDSType) {
				if ($tmpDSType) {
					$dataSourcesOptions [] = JHTML::_ ( 'select.option', '</OPTGROUP>' );
				}
				$dataSourcesOptions [] = JHTML::_ ( 'select.option', '<OPTGROUP>', strtoupper($dataSource->type) );
				$lastDSType = $dataSource->type;
				$tmpDSType = $dataSource->type;
			}
				
			$dataSourcesOptions [] = JHTML::_ ( 'select.option', $dataSource->id, $dataSource->name );
		}
		if ($lastDSType !== null) {
			$dataSourcesOptions [] = JHTML::_ ( 'select.option', '</OPTGROUP>' );
		}
		
		return JHtml::_('select.genericlist', $dataSourcesOptions, $this->name. '[]', 'multiple="multiple" size="20" style="width: 250px"', 'value', 'text', $this->value);
	} 
}
