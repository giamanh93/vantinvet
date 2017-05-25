<?php
/**
 * field tos
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */

defined('_JEXEC') or die('Restricted access');
$_prefix = $viewData['prefix'];
$field = $viewData['field'];
//$userData = $viewData['userData'];
$app = JFactory::getApplication();
if($app->isSite()){
	vmJsApi::popup('#full-tos','#terms-of-service');
	if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
	$cart = VirtuemartCart::getCart();
	$cart->prepareVendor();
	if(is_array($cart->BT) and !empty($cart->BT['tos'])){
		$tos = $cart->BT['tos'];
	} else {
		$tos = 0;
	}
} else {
	$tos = $field['value'];
}

if(!class_exists('VmHtml')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
echo VmHtml::checkbox ($_prefix.$field['name'], $tos, 1, 0, 'class="terms-of-service"');

if (VmConfig::get ('oncheckout_show_legal_info', 1) and $app->isSite()) {
?>
<div class="terms-of-service">
	<label>
		<a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=1', FALSE) ?>" class="terms-of-service" id="terms-of-service"
		   target="_blank">
			<span class="vmicon vm2-termsofservice-icon"></span>
			<?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED') ?>
		</a>
	</label>

	<div id="full-tos">
		<h2><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOS') ?></h2>
		<?php echo $cart->vendor->vendor_terms_of_service ?>
		</div>
</div>
<?php
}
?>