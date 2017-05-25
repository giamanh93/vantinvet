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
class TableSources extends JTable {
	/**
	 * @var int
	 */
	public $id = null;
	
	/**
	 * @var string
	 */
	public $type = 'user';
	
	/**
	 * @var string
	 */
	public $name = '';
	
	/**
	 * @var string
	 */
	public $description = '';
	
	/**
	 * @var int
	 */
	public $checked_out = 0;
	
	/**
	 * @var datetime
	 */
	public $checked_out_time = 0;
	
	/**
	 * @var int
	 */
	public $published = 1;
	
	/**
	 * @var int
	 */
	public $ordering = null;
	
	/**
	 * @var string
	 */
	public $sqlquery = '';
	
	/**
	 * @var string
	 */
	public $sqlquery_managed = '{}';
	
	/**
	 * @var string
	 */
	public $params = null;
	
	/**
	 * Bind Table override
	 * @override
	 * 
	 * @see JTable::bind()
	 */
	public function bind($fromArray, $saveTask = false, $sessionTask = false) {
		parent::bind ( $fromArray );
		
		if ($saveTask) {
			$registry = new JRegistry ();
			$registry->loadArray ( $this->params );
			$this->params = $registry->toString ();
			
			if (is_array ( $this->sqlquery_managed )) {
				$this->sqlquery_managed = json_encode ( $this->sqlquery_managed );
			}
		}
		
		// Manage complex attributes during session recovering bind/load
		if($sessionTask) {
			$registry = new JRegistry ( $this->params );
			$this->params = $registry;
				
			// By default convert to plain object this json serialized field, later convertable in JRegistry if needed
			if ($this->sqlquery_managed) {
				$this->sqlquery_managed = (object) ( $this->sqlquery_managed );
			}
		}
		
		return true;
	}
	
	/**
	 * Load Table override
	 * @override
	 * 
	 * @see JTable::load()
	 */
	public function load($idEntity = null, $reset = true) {
		// If not $idEntity set return empty object
		if($idEntity) {
			if(!parent::load ( $idEntity )) {
				return false;
			}
		}

		$registry = new JRegistry ();
		$registry->loadString( $this->params );
		$this->params = $registry;
		
		// By default convert to plain object this json serialized field, later convertable in JRegistry if needed
		if ($this->sqlquery_managed) {
			$this->sqlquery_managed = json_decode ( $this->sqlquery_managed );
		}
		
		return true;
	}
	
	/**
	 * Check Table override
	 * @override
	 * 
	 * @see JTable::check()
	 */
	public function check() {
		// Name required
		if (! $this->name) {
			$this->setError ( JText::_('COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Validate sql query managed chunks
		if($this->type == 'user') {
			if(isset($this->sqlquery_managed)) {
				$sqlQuerymanagedObject = json_decode($this->sqlquery_managed);
				if(	!($sqlQuerymanagedObject->option) ||
					!($sqlQuerymanagedObject->table_maintable) ||
					!($sqlQuerymanagedObject->titlefield) ||
					!($sqlQuerymanagedObject->id)) {
						$this->setError ( JText::_('COM_JMAP_ERROR_DATASOURCE_VALIDATION' ) );
						return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Class constructor
	 * @param Object& $_db
	 * 
	 * return Object&
	 */
	public function __construct(&$_db) {
		parent::__construct ( '#__jmap', 'id', $_db );
	}
}