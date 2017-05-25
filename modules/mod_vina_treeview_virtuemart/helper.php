<?php
/*
# ------------------------------------------------------------------------
# Module: Vina Treeview for VirtueMart
# ------------------------------------------------------------------------
# Copyright (C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://VinaGecko.com
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modVinaTreeViewVMartHelper
{
	public static function countProductsinCategory($cid)
	{
		$db = JFactory::getDBO();
		
		$cid = modVinaTreeViewVMartHelper::getChildCategory($cid, array($cid));
		$cid = implode(", ", $cid);
		
		$query = 'SELECT count(#__virtuemart_products.virtuemart_product_id) AS total
			FROM `#__virtuemart_products`, `#__virtuemart_product_categories`
			WHERE `#__virtuemart_products`.`virtuemart_vendor_id` = "1"
			AND `#__virtuemart_product_categories`.`virtuemart_category_id` IN (' . $cid . ')
			AND `#__virtuemart_products`.`virtuemart_product_id` = `#__virtuemart_product_categories`.`virtuemart_product_id`
			AND `#__virtuemart_products`.`published` = "1" ';
			
		$db->setQuery($query);
		$count = $db->loadResult();
		
		return $count;
	}
	
	public static function getChildCategory($pid, $cid = array())
	{
		$categoryModel 	= VmModel::getModel('Category');
		$categories 	= $categoryModel->getChildCategoryList(1, $pid);
		
		if(count($categories))
		foreach($categories as $item) {
			$id = $item->virtuemart_category_id;
			$cid[] = $id;
			$cid = modVinaTreeViewVMartHelper::getChildCategory($id, $cid);
		}
		
		return $cid;
	}
	
	public static function getCopyrightText($module)
	{
		echo '<div id="vina-copyright'.$module->id.'">© Free <a href="http://vinagecko.com/joomla-modules" title="Free Joomla! 3 Modules">Joomla! 3 Modules</a>- by <a href="http://vinagecko.com/" title="Beautiful Joomla! 3 Templates and Powerful Joomla! 3 Modules, Plugins.">VinaGecko.com</a></div>';
	}
}