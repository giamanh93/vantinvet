<?php
// namespace administrator\components\com_jmap\tables;
/**
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * ORM Table for sitemap sources
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage tables
 * @since 1.0
 */
class TableMenuPriorities extends JTable {
	/**
	 * @var int
	 */
	public $id = null;
	
	/**
	 * @var string
	 */
	public $priority = null;
	
	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false, $currentID = null) {
		// Initialise variables.
		$k = $this->_tbl_key;
		
		// Must be set a primary key and priority to store/update record
		if(!$this->$k && !$currentID || !$this->priority) {
			throw new JMapException(JText::_('COM_JMAP_VALIDATON_ERROR_MISSING_FIELDS'), 'warning');
		}
	
		// If a primary key really exists in DB as numeric and not autoincrement update the object, otherwise insert it.
		if ($this->$k > 0) {
			$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		} else {
			$this->id = (int)$currentID;
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}
	
		return $stored;
	}
	
	/**
	 * Class constructor
	 * @param Object& $_db
	 * 
	 * return Object&
	 */
	public function __construct(&$_db) {
		parent::__construct ( '#__jmap_menu_priorities', 'id', $_db );
	}
}