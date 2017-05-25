<?php
// namespace administrator\components\com_jmap\framework\plugins;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage plugins
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * External plugins interface for sitemap data
 * It's the contract that must be implemented by every plugin class
 * that retrieves data in an arbitrary way or resource and returns them
 * following a specific format to render the sitemap in every format supported HTML, XML, etc
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage plugins
 * @since 3.3
 */
interface JMapFilePlugin {
	/**
	 * Retrieves records for the plugin data source using whatever way and resource is required
	 * Formats and returns an associative array of data based on the following scheme  
	 *
	 * @param JRegistry The object holding configuration parameters for the plugin and data source
	 * @param JDatabase $db The database connector object
	 * @param JMapModel $sitemapModel The sitemap model object reference, it's needed to manage limitStart, limitRows properties and affected_rows state
	 *        	
	 * @return array
	 * This function must return an associative array as following:
	 * $returndata['items'] -> It's the mandatory objects array of elements, it must contain at least title and routed link fields
	 * $returndata['items_tree'] -> Needed to render elements grouped by cats with a nested tree, not mandatory
	 * $returndata['categories_tree'] -> Needed to render elements grouped by cats with a nested tree, not mandatory
	 * 
	 * $returndata['items'] must contain records objects with following properties (* = required)
	 * 						->title * A string for the title
	 * 						->link * A string for the link
	 * 						->lastmod (used for XML sitemap) A date string in MySql format yyyy-mm-dd hh:ii:ss
	 * 						->metakey (used for Google news sitemap) A string for metakeys of each record
	 * 						->publish_up (used for Google news sitemap) A date string in MySql format yyyy-mm-dd hh:ii:ss
	 * 						->access (used for Google news sitemap, >1 = registration access) An integer for Joomla! access level of each record
	 * 
	 * $returndata['items_tree'] must be a numerical array that groups items by the containing category id, the index of the array is the category id 
	 * 
	 * $returndata['categories_tree'] must be a numerical array that groups categories by parent category, the index of the array is the category parent id,
	 * 								  the elements of the array must be records objects representing categories with following properties (* = required)
	 * 						->category_id * An integer for the category ID
	 * 						->category_title * A string for the category title
	 * 						->category_link * A string for the category link
	 * 						->lastmod (used for XML sitemap) A date string in MySql format yyyy-mm-dd hh:ii:ss
	 */
	public function getSourceData(JRegistry $pluginParams, JDatabase $db, JMapModel $sitemapModel);
}