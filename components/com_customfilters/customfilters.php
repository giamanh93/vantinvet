<?php
/**
 *
 * Customfilters entry point
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
 * @version $Id: customfilters.php 2014-03-10 13:47:00Z sakis $
 */

// no direct access
defined('_JEXEC') or die;


// Include dependencies
jimport('joomla.application.component.controller');

if(!defined('JPATH_VM_ADMIN')) define('JPATH_VM_ADMIN',JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart');
//load the Virtuemart configuration
require_once(JPATH_VM_ADMIN.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
VmConfig::loadConfig();

if (!class_exists( 'VmCompatibility' )) require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customfilters'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmcompatibility.php');

if(!defined('JPATH_VM_SITE')) define('JPATH_VM_SITE',JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart');
if(!defined('JPATH_CF_MODULE')) define('JPATH_CF_MODULE',JPATH_ROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_cf_filtering');
require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'tools.php';
require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'input.php';

$input=JFactory::getApplication()->input; 
$controller = JControllerLegacy::getInstance('Customfilters');
$controller->execute($input->get('task','display','cmd'));
$controller->redirect();
