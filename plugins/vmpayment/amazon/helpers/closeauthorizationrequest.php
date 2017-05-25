<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
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
class amazonHelperCloseAuthorizationRequest extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_CloseAuthorizationRequest $closeAuthorizationRequest, $method) {
		parent::__construct($closeAuthorizationRequest, $method);
	}



	function getContents () {

		$contents = $this->tableStart("captureRequest");
		$contents .= $this->getRow("Dump: ", var_export($this->amazonData, true));
		$contents .= $this->tableEnd();

		return $contents;
	}


}