<?php
/**
 * @package		mod_qlform
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class modQlFormDatabase
{

    /**
     * Method for getting database fields
     *
     * @param   string  $database  database name
     * @param   string  $table      Name of table to save data in
     *
     * @return  bool true on success, false on failure
     *
     */
    function getDatabase()
    {
       return JFactory::getDBO();
    }

	/**
	 * Method for storing data to database
	 *
	 * @param   string    $table      Name of table to save data in
	 * @param   array  $data  data to be stored 
	 *
	 * @return  mixed A database resource if successful, FALSE if not.
	 *
	 */
	public function save($table,$data)
	{
		$db=$this->getDatabase();
        $data=$this->objectToArrayOrTheOtherWay($data);
        return $db->insertObject($table,$data);
	}

	/**
	 * Method for getting database fields 
	 *
	 * @param   string  $database  database name 
	 * @param   string  $table      Name of table to save data in
	 *
	 * @return  bool true on success, false on failure
	 *
	 */	
	function getDatabaseFields($database,$table)
	{
        $db=$this->getDatabase();
		$db->setQuery('SHOW COLUMNS FROM `'.$table.'` FROM `'.$database.'` ');
		$db->query(); 
		return $db->loadObjectList();
	}
	/**
	 * Method for checking if table exists 
	 *
	 * @param   string  $database  database name 
	 * @param   string   $table      Name of table to save data in
	 *
	 * @return  string insert query
	 *
	 */	
	function tableExists($database,$table)
	{
        $db=$this->getDatabase();
		$db->setQuery('SHOW TABLES FROM `'.$database.'`');
		$db->query(); 
		foreach ($db->loadObjectList() as $k=>$v) foreach ($v as $v2) $arr[$k]=$v2;
        if (is_array($arr) AND in_array($table,$arr)) return true;
		else return false;
	}
	/**
	 * Method for getting Joomla! table name  
	 *
	 * @return  string table name
	 *
	 */	
	function getTableName($table)
	{
		if (preg_match('/#__/',$table)) $table=$this->getPrefix().substr($table,3);
		return $table;
	}
	/**
	 * Method for getting Joomla! database name  
	 *
	 * @return  string database name
	 *
	 */	
	function getDatabaseName()
	{
		$config=JFactory::getConfig();
        return $config->get('db');
	}
	/**
	 * Method for getting Joomla! prefix name  
	 *
	 * @return  string database name
	 *
	 */	
	function getPrefix()
	{
		$config=JFactory::getConfig();
		return $config->get('dbprefix');
	}
	/**
	 * Method to turn object to array with database fields 
	 *
	 * @param   string  $database  database name 
	 * @param   string    $table      Name of table to save data in
	 *
	 * @return  string insert query
	 *
	 */	
	function databaseFieldsObjectToArray($arrFields)
	{
		foreach ($arrFields as $k=>$v) $arr[]=$v->Field;
		return array_flip($arr);
	}	
	/**
	 * Method to turn object to array 
	 *
	 * @param   string  $database  database name 
	 * @param   string    $table      Name of table to save data in
	 *
	 * @return  string insert query
	 *
	 */	
	function objectToArrayOrTheOtherWay($input)
	{
		if (is_object ($input)) foreach ($input as $k=>$v) $output[$k]=$v;
		if (is_array ($input)) {$output=new StdClass();foreach ($input as $k=>$v) $output->$k=$v;}
		return $output;
	}
}