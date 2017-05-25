<fieldset class="vm-fieldset-pricelist">
	<table class="cart-summary" style="cellspacing:0; cellpadding:0; width:100%; ">
		<!-- Table Header -->
		<thead>
			<tr class="first last">
				<th class="tb-image" style="width: 15%">&nbsp;</th>
				<th class="tb-name" style="width: 40%"><span class="nobr"><?php echo vmText::_ ('COM_VIRTUEMART_CART_NAME') ?></span></th>
				<th class="tb-sku" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_SKU') ?></th>
				<th class="tb-price a-center" style="width: 10%"><span class="nobr"><?php echo vmText::_ ('COM_VIRTUEMART_CART_PRICE') ?></span></th>
				<th class="tb-quantity a-center" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_QUANTITY') ?></th>
				<th class="tb-subtotal a-center" style="width: 10%"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?></th>
				<th class="tb-delete a-center" style="width: 5%">&nbsp;</th>
			</tr>
		</thead>
		
		<!-- Table Body -->
		<tbody>
			<?php
			$i = 1;
			foreach ($this->cart->products as $pkey => $prow) { ?>
			<tr class="sectiontableentry<?php echo $i ?>">
				<td>
					<?php if ($prow->virtuemart_media_id) { ?>
						<span class="cart-images">
							<?php
							if (!empty($prow->images[0])) {
								echo $prow->images[0]->displayMediaThumb ('', FALSE);
							}
							?>	
						</span>
					<?php } ?>
				</td>
				<td>
					<div class="product-name">
						<?php echo JHtml::link ($prow->url, $prow->product_name);
							echo $this->customfieldsModel->CustomsFieldCartDisplay ($prow);
						?>
					</div>
				</td>
				<td class="a-center">
					<?php  echo $prow->product_sku ?>
				</td>
				<td class="a-right">
					<?php
					/*if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
						echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
					}
					elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
						echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
					}*/
					echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, 1) ?>
				</td>		
				<!-- inclusive price starts here -->
				<td class="a-center">
				<?php
					if ($prow->step_order_level) $step=$prow->step_order_level;
					else $step=1;
					if($step==0)
						$step=1;
				?>
				<div class="vir_quantity">
				   <input type="text"
						  onblur="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
						  onclick="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
						  onchange="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
						  onsubmit="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
						  title="<?php echo  vmText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="quantity-input js-recalculate" size="3" maxlength="4" name="quantity[<?php echo $pkey; ?>]" value="<?php echo $prow->quantity ?>" />
					<button type="submit" class="icon icon-refresh vm2-add_quantity_cart" name="updatecart.<?php echo $pkey ?>" title="<?php echo  vmText::_ ('COM_VIRTUEMART_CART_UPDATE') ?>"></button>
				</div>
				</td>
				<!--Sub total starts here -->
				<td class="a-right">
					<?php
					/*
					if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
						echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
					}
					elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
						echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span>';
					}*/
					echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, $prow->quantity) ?>
				</td>
				<td class="a-center last">
					<button type="submit" class="icon icon-trash-o vm2-remove_from_cart" name="delete.<?php echo $pkey ?>" title="<?php echo vmText::_ ('COM_VIRTUEMART_CART_DELETE') ?>"></button>
				</td>
			</tr>		
			<?php
				$i = ($i==1) ? 2 : 1;
			} ?>
			
			<!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
			<?php if (VmConfig::get ('show_tax')) {
				$colspan = 3;
			} else {
				$colspan = 2;
			} ?>
			<tr class="tb-total">
				<td colspan="7" class="total-title">
					<div class="vm-continue-shopping">
						<?php // Continue Shopping Button
						if (!empty($this->continue_link_html)) {
							echo $this->continue_link_html;
						} ?>
					</div>
					<div class="total-block">
						<div class="title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL').': '; ?></div>
						<div class="total" >
							<?php if (VmConfig::get ('show_tax')) { ?>
								<?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('taxAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?>
							<?php } ?>
							<?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('discountAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?>
							<?php echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $this->cart->cartPrices, FALSE) ?>
							
						</div>
					</div>
				</td>
			</tr>
		</tbody>
		
		<!-- Table Footer -->
		<tfoot>
		<?php if (VmConfig::get ('coupons_enable')) { ?>
			<tr class="sectiontableentry2">
				<td colspan="7" class="text-left">
					<div class="tb-tfoot">
						<?php if (!empty($this->layoutName) && $this->layoutName == 'default') {
							echo $this->loadTemplate ('coupon');
						} ?>
						<?php if (!empty($this->cart->cartData['couponCode'])) {					
							echo $this->cart->cartData['couponCode'];
							echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')') : '';
						?>
					</div>
					<?php if (VmConfig::get ('show_tax')) { ?>
					<div class="text-right">
						<?php echo $this->currencyDisplay->createPriceDiv ('couponTax', '', $this->cart->cartPrices['couponTax'], FALSE); ?> 
					</div>
					<?php } ?>					
					<div class="text-right">
						<?php echo $this->currencyDisplay->createPriceDiv ('salesPriceCoupon', '', $this->cart->cartPrices['salesPriceCoupon'], FALSE); ?>
					</div>
				</td>				
				<?php } else { ?>
					</div>
				</td>				
				<?php } ?>
			</tr>
		<?php } ?>
		<?php foreach ($this->cart->cartData['DBTaxRulesBill'] as $rule) { ?>
			<tr class="sectiontableentry<?php echo $i ?>">
				<td colspan="4" class="text-right"><?php echo $rule['calc_name'] ?> </td>
				<?php if (VmConfig::get ('show_tax')) { ?>
				<td class="text-right"></td>
				<?php } ?>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?></td>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
			</tr>
			<?php 
				if ($i) { $i = 1;} 
				else { $i = 0;}
			?>
		<?php } ?>
		
		<?php foreach ($this->cart->cartData['taxRulesBill'] as $rule) { ?>
			<tr class="sectiontableentry<?php echo $i ?>">
				<td colspan="4" class="text-right"><?php echo $rule['calc_name'] ?> </td>
				<?php if (VmConfig::get ('show_tax')) { ?>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
				<?php } ?>
				<td class="text-right"><?php ?> </td>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
			</tr>
			<?php
			if ($i) {
				$i = 1;
			} else {
				$i = 0;
			}
		} ?>
		<?php foreach ($this->cart->cartData['DATaxRulesBill'] as $rule) { ?>
			<tr class="sectiontableentry<?php echo $i ?>">
				<td colspan="4" align="right"><?php echo   $rule['calc_name'] ?> </td>
				<?php if (VmConfig::get ('show_tax')) { ?>
				<td class="text-right"></td>
				<?php } ?>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?>  </td>
				<td class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
			</tr>
			<?php
			if ($i) {
				$i = 1;
			} else {
				$i = 0;
			}
		} ?>
		<?php if ( 	VmConfig::get('oncheckout_opc',true) or !VmConfig::get('oncheckout_show_steps',false) or (!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) and !empty($this->cart->virtuemart_shipmentmethod_id))) { ?>
			<tr class="sectiontableentry1">
				<?php if (!$this->cart->automaticSelectedShipment) { ?>
					<td class="shipment" colspan="7">
						<div class="sectiontableentry1-inner">
						<?php
						echo $this->cart->cartData['shipmentName'].'<br/>';
						if (!empty($this->layoutName) and $this->layoutName == 'default') {
							if (VmConfig::get('oncheckout_opc', 0)) {
								$previouslayout = $this->setLayout('select');
								echo $this->loadTemplate('shipment');
								$this->setLayout($previouslayout);
							} else {
								echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=edit_shipment', $this->useXHTML, $this->useSSL), $this->select_shipment_text, 'class=""');
							}
						} else {
							echo vmText::_ ('COM_VIRTUEMART_CART_SHIPPING');
						} ?>
						<?php if (VmConfig::get ('show_tax')) { ?>
							<div class="text-right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('shipmentTax', '', $this->cart->cartPrices['shipmentTax'], FALSE) . "</span>"; ?> </div>
						<?php } ?>
						<div class="text-right"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?></div>
						<div class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?> </div>
						</div>
					</td>
				<?php } else { ?>
					<td colspan="7">
						<div class="sectiontableentry1-inner">
						<?php echo $this->cart->cartData['shipmentName']; ?>
						<?php if (VmConfig::get ('show_tax')) { ?>
						<div class="text-right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('shipmentTax', '', $this->cart->cartPrices['shipmentTax'], FALSE) . "</span>"; ?> </div>
						<?php } ?>
						<div class="text-right"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?></div>
						<div class="text-right"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?> </div>
						</div>
					</td>
				<?php } ?>			
			</tr>
		<?php } ?>
		<?php if ($this->cart->pricesUnformatted['salesPrice']>0.0 and (VmConfig::get('oncheckout_opc',true) or !VmConfig::get('oncheckout_show_steps',false) or ( (!VmConfig::get('oncheckout_opc',true) and VmConfig::get('oncheckout_show_steps',false) ) and !empty($this->cart->virtuemart_paymentmethod_id)))) { ?>
			<tr class="sectiontableentry1">
				<?php if (!$this->cart->automaticSelectedPayment) { ?>
					<td class="payment" colspan="7">
						<div class="sectiontableentry1-inner">
							<div class="sectiontableentry1_paymentname">
							<?php echo $this->cart->cartData['paymentName'].'<br/>';?>
							</div>
							<?php
							if (!empty($this->layoutName) && $this->layoutName == 'default') { ?>
								<div class="sectiontableentry1_paymentname">
								<?php 
								if (VmConfig::get('oncheckout_opc', 0)) {
									$previouslayout = $this->setLayout('select');
									echo $this->loadTemplate('payment');
									$this->setLayout($previouslayout);
								} else {
									echo JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment', $this->useXHTML, $this->useSSL), $this->select_payment_text, 'class=""');
								} ?>
								</div>
							<?php } else { ?>
								<div class="paymentshow_cart_payment" >
									<?php echo vmText::_ ('COM_VIRTUEMART_CART_PAYMENT'); ?>
								</div>
							<?php } ?>
							<?php if (VmConfig::get ('show_tax')) { ?>
							<div class="paymentshow_tax"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('paymentTax', '', $this->cart->cartPrices['paymentTax'], FALSE) . "</span>"; ?> </div>
							<?php } ?>
							<div class="paymentsalesprice"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?></div>
							<div class="paymentsalesprice"><?php  echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?> </div>
						</div>
					</td>		
				<?php } else { ?>
					<td colspan="7">
						<div class="sectiontableentry1-inner">
							<div class="sectiontableentry1_paymentname">
							<?php echo $this->cart->cartData['paymentName']; ?>
							</div>
							<?php if (VmConfig::get ('show_tax')) { ?>
							<div class="paymentshow_tax"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('paymentTax', '', $this->cart->cartPrices['paymentTax'], FALSE) . "</span>"; ?> </div>
							<?php } ?>
							<div class="paymentsalesprice"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?></div>
							<div class="paymentsalesprice"><?php  echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?> </div>
						</div>
					</td>					
				<?php } ?>
			</tr>
		<?php  } ?>			
		<?php if ($this->totalInPaymentCurrency) { ?>
			<tr class="sectiontableentry2">
				<td colspan="4" align="right"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>:</td>
				<?php if (VmConfig::get ('show_tax')) { ?>
				<td class="text-right"></td>
				<?php } ?>
				<td class="text-right"></td>
				<td class="text-right"><strong><?php echo $this->totalInPaymentCurrency;   ?></strong></td>
			</tr>
		<?php } ?>
		</tfoot>
	</table>
</fieldset>