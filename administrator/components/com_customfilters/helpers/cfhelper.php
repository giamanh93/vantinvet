<?php
/**
 *
 * A helper class offering some usefull functions
 *
 * @package 	customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2013 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: cfhelper.php 2013-8-27 19:44 sakis $
 * @since		1.9.0
 */


defined('_JEXEC') or die('Restricted access');
class cfHelper{

	public static $counter = 0;
	public static $categories=array();

	public static $categoryTree = array();


	private static function loadConfig()
	{
		$db = JFactory::getDbo();

		$sql = $db->getQuery(true)
		->select($db->quoteName('params'))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('element') . " = " . $db->quote('com_customfilters'));
		$db->setQuery($sql);
		$config_ini = $db->loadResult();

		// OK, Joomla! 1.6 stores values JSON-encoded so, what do I do? Right!
		$config_ini = json_decode($config_ini, true);
		if (is_null($config_ini) || empty($config_ini))
		{
			$config_ini = array();
		}

		return $config_ini;
	}


	public static function getValue($key, $default){

		static $config;
		if (empty($config))
		{
			$config = self::loadConfig();
		}

		if (array_key_exists($key, $config))
		{
			return $config[$key];
		}
		else
		{
			return $default;
		}
	}
	
	/**
	 * Returns the category tree and checks if there is a cached tree too
	 *
	 * @param array $selectedCategories
	 * @param int $cid
	 * @param int $level
	 * @param array $disabledFields
	 */
	static public function categoryListTree ($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields = array()) {
		$category_key=md5(serialize($selectedCategories).$cid.$level.serialize($disabledFields));
		$app=JFactory::getApplication();
		$vendorId=1;		
		if (empty(self::$categoryTree[$category_key])) {
			$cache = JFactory::getCache ('_virtuemart');
			$cache->setCaching (1);
			self::$categoryTree[$category_key] = $cache->call (array('ShopFunctions', 'categoryListTreeLoop'), $selectedCategories, $cid, $level, $disabledFields,$clean_cache=true,$app->isSite(),$vendorId);
		}
		return self::$categoryTree[$category_key];
	}

	/**
	 * Creates structured option fields for all categories
	 *
	 * @todo: Connect to vendor data
	 * @author Max Milbers, jseros, Sakis Terz
	 * @param array 	$selectedCategories All category IDs that will be pre-selected
	 * @param int 		$cid 		Internally used for recursion
	 * @param int 		$level 		Internally used for recursion
	 * @param boolean
	 * @return string 	$category_tree HTML: Category tree list
	 * @see	ShopFunctions
	 */
	static public function categoryListTreeLoop ($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields = array(),$clean_cache=false) {
		$category_key=md5(serialize($selectedCategories).$cid.$level.serialize($disabledFields));

		self::$counter++;

		static $categoryTree = '';
		if($clean_cache)$categoryTree=''; //clean the previous cached $categoryTree

		$virtuemart_vendor_id = 1;

		// 		vmSetStartTime('getCategories');
		$categoryModel = VmModel::getModel ('category');
		$level++;

		$categoryModel->_noLimit = TRUE;
		if(self::$categories){
			$app = JFactory::getApplication ();
			self::$categories = $categoryModel->getCategories ($app->isSite (), $cid);
		}
		$records=self::$categories;
		$selected = "";
		if (!empty($records)) {
			foreach ($records as $key => $category) {

				$childId = $category->category_child_id;

				if ($childId != $cid) {
					if (in_array ($childId, $selectedCategories)) {
						$selected = 'selected=\"selected\"';
					} else {
						$selected = '';
					}

					$disabled = '';
					if (in_array ($childId, $disabledFields)) {
						$disabled = 'disabled="disabled"';
					}

					if ($disabled != '' && stristr ($_SERVER['HTTP_USER_AGENT'], 'msie')) {
						//IE7 suffers from a bug, which makes disabled option fields selectable
					} else {
						$categoryTree .= '<option ' . $selected . ' ' . $disabled . ' value="' . $childId . '">';
						$categoryTree .= str_repeat (' - ', ($level - 1));

						$categoryTree .= $category->category_name . '</option>';
					}
				}

				if ($categoryModel->hasChildren ($childId)) {
					self::categoryListTreeLoop ($selectedCategories, $childId, $level, $disabledFields);
				}
			}
		}
		return $categoryTree;
	}
}