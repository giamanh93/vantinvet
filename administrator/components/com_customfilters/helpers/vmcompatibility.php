<?php
/**
 *
 * VM compatibility layer
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @version $Id: vmCompatibility.php  2014-06-03 13:28:00Z sakis $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
VmConfig::loadConfig();

class VmCompatibility extends JObject{

	public static $vmcompatibility = null;
	private $_tableColumns=array();

	public $vm3_table_columns=array('virtuemart_product_customfields'=>array(
	 'custom_value'=>'customfield_value',
	 'custom_price'=>'customfield_price',
	 'custom_param'=>'customfield_params'),
	'virtuemart_customs'=>array(
		'custom_field_desc'=>'custom_desc',		
	)
	);



	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $properties  Either and associative array or another
	 *                              object to set the initial properties of the object.
	 *
	 * @since   2.0.0
	 */
	public function __construct($properties = array())
	{
		if( empty($properties) || !is_array($properties))$properties=array();
		parent::__construct($properties);
		$this->set('vmVersion',VmConfig::getInstalledVersion());
	}


	/**
	 * Creates the vmcompatibility object only once
	 *
	 *@since 	2.0
	 *@author	Sakis Terzis
	 */
	public static function getInstance(){
		if(!self::$vmcompatibility){
			self::$vmcompatibility=new vmCompatibility();
		}
		return self::$vmcompatibility;
	}

	/**
	 * This function will return the column name of the current db tables, even if it takes an older column name as param
	 *
	 * @param strin 	$column The name of the column either VM2 or VM3
	 * @param string 	$table  The name of the table to search
	 * @return	string	$column	The column name of the current table
	 */
	public function getColumnName($column='virtuemart_customfield_id',$table='virtuemart_product_customfields'){
		$column=trim((string)$column);
		$table=trim((string)$table);
		//remove the prefix from the table if set
		$table=preg_replace('/#__/', '', $table);

		//the table does not exist
		if(!isset($this->vm3_table_columns[$table]))return $column;
			
		$version=$this->get('vmVersion');

		
		//VM3
		if(version_compare($version,'2.9' ,'ge')>0){
			//keys are the VM2 fields
			$key_exist=array_key_exists($column, $this->vm3_table_columns[$table]);
			//if !false then it uses the VM2 table columns
			if($key_exist!==false)return $this->vm3_table_columns[$table][$column];
			else return $column;
		}
		//VM2
		else{
			//values are the VM3 fields
			$value_exist=array_search($column, $this->vm3_table_columns[$table]);
			if($value_exist!==false)return $value_exist;
			else return $column;
		}
		return $column;

	}


	/**
	 * Get the columns of the current table
	 *
	 * @param string $table
	 */
	private function _getTableColumns($table='#__virtuemart_product_customfields'){
		if(empty($this->_tableColumns[$table])){
			$db=JFactory::getDbo();
			$query='SHOW COLUMNS FROM '.$db->quoteName($table);
			$db->setQuery($query);
			$this->_tableColumns[$table]=$db->loadColumn();
		}
		return $this->_tableColumns[$table];
	}
}