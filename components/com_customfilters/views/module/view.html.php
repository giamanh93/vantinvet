<?php
/**
 *
 * Customfilters module view
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: view.html.php 15 2012-10-8 20:00:00Z sakis $
 */

// No direct access
defined('_JEXEC') or die;

//import the view class
jimport('joomla.application.component.view');

class CustomfiltersViewModule extends JViewLegacy{


	public function display($tpl = null){
		//used to get the products ids in case of price filter usage
		//otherwise return false
		if(!class_exists('CustomfiltersModelProducts')) require(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'products.php');
		$myProductModel=new CustomfiltersModelProducts();
		$myProductModel->getProductIdsFromSearches();		
		parent::display($tpl);
	}
}