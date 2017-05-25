<?php
// namespace administrator\components\com_jmap\tables;
/**
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * ORM Table for Datasets
 *
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage tables
 * @since 2.0
 */
class TableDatasets extends JTable {
	/**
	 *
	 * @var int
	 */
	public $id;
	
	/**
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 *
	 * @var string
	 */
	public $description;
	
	/**
	 *
	 * @var int
	 */
	public $checked_out = 0;
	
	/**
	 *
	 * @var datetime
	 */
	public $checked_out_time = 0;
	
	/**
	 *
	 * @var string
	 */
	public $sources = '[]';
	
	/**
	 * Check Table override
	 * @override
	 *
	 * @see JTable::check()
	 */
	public function check() {
		// Title required
		if (! $this->name) {
			$this->setError ( JText::_ ( 'COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Store Table override
	 * @override
	 *
	 * @see JTable::store()
	 */
	public function store($updateNulls = false) {
		$result = parent::store($updateNulls);
		
		// If store sucessful go on to popuplate relations table for sources/datasets
		if($result) {
			// Clear table from previous records
			$queryDelete = "DELETE" .
						   "\n FROM " . $this->_db->quoteName('#__jmap_dss_relations') .
						   "\n WHERE" .
						   "\n " . $this->_db->quoteName('datasetid') . " = " .
						   "\n " . (int)$this->id;
			if(!$this->_db->setQuery($queryDelete)->execute()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			
			// Manage multiple tuples to be inserted using single query
			$selectedSources = json_decode($this->sources);
			if(count($selectedSources)) {
				$insertTuples = array();
				foreach ($selectedSources as $source) {
					$insertTuples[] = '(' . (int)$this->id . ',' . $source . ')';
				}
				$insertTuples = implode(',', $insertTuples);
				
				$queryMultipleInsert = "INSERT" .
									   "\n INTO " . $this->_db->quoteName('#__jmap_dss_relations') .
									   "\n (" . 
									   $this->_db->quoteName('datasetid') . "," .
									   $this->_db->quoteName('datasourceid') . ")" .
									   "\n VALUES " . $insertTuples;
				if(!$this->_db->setQuery($queryMultipleInsert)->execute()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete Table override
	 * @override
	 *
	 * @see JTable::delete()
	 */
	public function delete($pk = null) {
		$result = parent::delete($pk);
		
		// If store sucessful go on to popuplate relations table for sources/datasets
		if($result) {
			// Clear table from previous records
			$queryDelete = "DELETE" .
						   "\n FROM " . $this->_db->quoteName('#__jmap_dss_relations') .
						   "\n WHERE" .
						   "\n " . $this->_db->quoteName('datasetid') . " = " .
						   "\n " . (int)$this->id;
			if(!$this->_db->setQuery($queryDelete)->execute()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		
		return $result;
	}
	
	/**
	 * Class constructor
	 * 
	 * @param Object& $_db
	 *        	return Object&
	 */
	public function __construct(&$_db) {
		parent::__construct ( '#__jmap_datasets', 'id', $_db );
	}
}