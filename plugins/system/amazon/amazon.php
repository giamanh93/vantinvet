<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage system
 * @version $Id$
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - April 20 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
class PlgSystemAmazon extends JPlugin {

	function __construct (& $subject, $config) {
		parent::__construct($subject, $config);

	}

	function onAfterRender () {
		$fileName = 'touch.php';
		$tstamp = @filemtime($fileName);
		if ($tstamp !== false) {
			$now = time();
			$difference = abs($now - $tstamp);
			$frequency = $this->params->get('frequency');
			if ($difference > $frequency) {
				JLoader::import('joomla.plugin.helper');
				JPluginHelper::importPlugin('vmpayment');
				$app = JFactory::getApplication();
				$app->triggerEvent('plgVmRetrieveIPN', array());
			};
		}
		@touch($fileName);

	}
}