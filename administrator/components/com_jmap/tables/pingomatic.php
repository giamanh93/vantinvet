<?php
// namespace administrator\components\com_jmap\tables;
/**
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * ORM Table for Pingomatic urls
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage tables
 * @since 2.0
 */
class TablePingomatic extends JTable{
	/**
	 * @var int
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $title;
	
	/**
	 * @var string
	 */
	public $blogurl;
	
	/**
	 * @var string
	 */
	public $rssurl;
	
	/**
	 * @var string
	 */
	public $services = '{}';
	
	/**
	 * @var datetime
	 */
	public $lastping;
	
	/**
	 * @var int
	 */
	public $checked_out = 0;
	
	/**
	 * @var datetime
	 */
	public $checked_out_time = 0;

	/**
	 * Bind Table override
	 * @override
	 * 
	 * @see JTable::bind()
	 */
	public function bind($fromArray, $saveTask = false, $sessionTask = false) {
		parent::bind ( $fromArray );
		
		if ($saveTask) {
			$services = array();
			foreach ($fromArray as $key => $value) {
				if (strpos($key, 'chk_') === 0) {
					$services[$key] = $value;
				}
			}
			if (is_array ( $services )) {
				$this->services = json_encode ( $services );
			}
		}
		
		// Manage complex attributes during session recovering bind/load
		if($sessionTask) {
			$services = array();
			foreach ($fromArray as $key => $value) {
				if (strpos($key, 'chk_') === 0) {
					$services[$key] = $value;
				}
			}
			$registry = new JRegistry ( $services );
			$this->services = $registry;
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
		
		// Decoding services on load and wrap into JRegistry object
		if ($this->services) {
			$this->services = json_decode ( $this->services );
			$servicesRegistry = new JRegistry();
			$servicesRegistry->loadObject($this->services);
			// New assignment
			$this->services = $servicesRegistry;
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
		// Title required
		if (! $this->title) {
			$this->setError ( JText::_('COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		// Check if the validation is enabled and not based on server settings limit management
		if(JComponentHelper::getParams('com_jmap')->get('resources_limit_management', 1)) {
			// Link url required and to be valid
			$blogurl = iconv("UTF-8", "ISO-8859-1//IGNORE", $this->blogurl);
			if (! $this->blogurl || !filter_var($blogurl, FILTER_VALIDATE_URL)) {
				$this->setError ( JText::_('COM_JMAP_VALIDATION_ERROR_URL' ) );
				return false;
			}
			
			// LinkRss url to be valid
			$rssurl = iconv("UTF-8", "ISO-8859-1//IGNORE", $this->rssurl);
			if ( $this->rssurl && !filter_var($rssurl, FILTER_VALIDATE_URL)) {
				$this->setError ( JText::_('COM_JMAP_VALIDATION_ERROR_URL' ) );
				return false;
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
		parent::__construct ( '#__jmap_pingomatic', 'id', $_db );
	}
}