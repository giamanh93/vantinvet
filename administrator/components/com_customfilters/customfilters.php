
<?php
/**
 *
 * Customfilters entry point
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: customfilters.php 2014-02-21 18:36:00Z sakis $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_customfilters')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
VmConfig::loadConfig();
if (!class_exists( 'cfHelper' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customfilters'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'cfhelper.php'); 
if (!class_exists( 'VmCompatibility' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customfilters'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmcompatibility.php');

//if joomla 2.5 load my bootstrap
if(version_compare(JVERSION, '3.0','lt')):
$doc=JFactory::getDocument();
$doc->addStylesheet(JURI::base().'components/com_customfilters/assets/css/mybootstrap.css');
endif;


// Add stylesheets and Scripts
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'administrator/components/com_customfilters/assets/css/display.css');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
// Include dependencies
jimport('joomla.application.component.controller');
$input=JFactory::getApplication()->input;

$controller = JControllerLegacy::getInstance('customfilters');
//$controller->execute(JRequest::getCmd('task'));
$controller->execute($input->get('task','display','cmd'));
$controller->redirect();
