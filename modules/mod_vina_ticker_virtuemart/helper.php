<?php
/*
# ------------------------------------------------------------------------
# Vina Product Ticker for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum: http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Load the language file of com_virtuemart.
VmConfig::loadJLang('com_virtuemart', true);

if(!class_exists('calculationHelper')) {
	require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/calculationh.php');
}
if(!class_exists('CurrencyDisplay')) {
	require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/currencydisplay.php');
}
if(!class_exists('VirtueMartModelVendor')) {
	require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/vendor.php');
}
if(!class_exists('VmImage')) {
	require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/image.php');
}
if(!class_exists('shopFunctionsF')) {
	require(JPATH_SITE . '/components/com_virtuemart/helpers/shopfunctionsf.php');
}
if(!class_exists('calculationHelper')) {
	require(JPATH_COMPONENT_SITE . '/helpers/cart.php');
}
if(!class_exists('VirtueMartModelProduct')) {
	JLoader::import('product', JPATH_ADMINISTRATOR . '/components/com_virtuemart/models');
}

class modVinaTickerVirtueMartHelper
{
	static function addtocart($product)
	{

		if(!VmConfig::get('use_as_catalog', 0))
		{
			$stockhandle = VmConfig::get('stockhandle', 'none');
			if(($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($product->product_in_stock - $product->product_ordered) < 1) 
			{
				$button_lbl  = vmText::_ ('COM_VIRTUEMART_CART_NOTIFY');
				$button_cls  = 'notify-button';
				$button_name = 'notifycustomer';
			?>
			<div style="display:inline-block;">
				<a href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id); ?>" class="notify"><?php echo vmText::_('COM_VIRTUEMART_CART_NOTIFY'); ?></a>
			</div>
			<?php } else { ?>
			<div class="addtocart-area">
				<form method="post" class="product" action="index.php">
					<?php if(!empty($product->customfieldsCart)) { ?>
					<div class="product-fields">
						<?php foreach($product->customfieldsCart as $field) { ?>
						<div style="display:inline-block;" class="product-field product-field-type-<?php echo $field->field_type; ?>">
							<?php if($field->show_title == 1) { ?>
								<span class="product-fields-title"><b><?php echo $field->custom_title; ?></b></span>
								<?php echo JHTML::tooltip ($field->custom_tip, $field->custom_title, 'tooltip.png'); ?>
							<?php } ?>
							<span class="product-field-display"><?php echo $field->display; ?></span>
							<span class="product-field-desc"><?php echo $field->custom_desc; ?></span>
						</div>
						<?php } ?>
					</div>
					<?php } ?>

					<div class="addtocart-bar">
						<span class="quantity-box">
							<input type="text" class="quantity-input" name="quantity[]" value="1"/>
						</span>
						<span class="quantity-controls">
							<input type="button" class="quantity-controls quantity-plus"/>
							<input type="button" class="quantity-controls quantity-minus"/>
						</span>
						<span class="addtocart-button">
							<?php echo shopFunctionsF::getAddToCartButton($product->orderable); ?>
						</span>
						<div class="clear"></div>
					</div>
					
					<input type="hidden" class="pname" value="<?php echo $product->product_name ?>"/>
					<input type="hidden" name="option" value="com_virtuemart"/>
					<input type="hidden" name="view" value="cart"/>
					<noscript><input type="hidden" name="task" value="add"/></noscript>
					<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>"/>
					<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>"/>
				</form>
				<div class="clear"></div>
			</div>
			<?php
			}
		}
	}
	
	public static function getCopyrightText($module)
	{
		echo '<div id="vina-copyright'.$module->id.'">© Free <a href="http://vinagecko.com/joomla-modules" title="Free Joomla! 3 Modules">Joomla! 3 Modules</a>- by <a href="http://vinagecko.com/" title="Beautiful Joomla! 3 Templates and Powerful Joomla! 3 Modules, Plugins.">VinaGecko.com</a></div>';
	}
}