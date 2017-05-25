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

$doc = JFactory::getDocument();
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.easing.min.js', 'text/javascript');
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.easy-ticker.js', 'text/javascript');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/style.css');
?>
<div class="vina-ticker-virtuemart-wrapper">
	<!-- Style Block -->
	<style type="text/css" scoped>
	#vina-ticker-virtuemart<?php echo $module->id; ?> {
		width: <?php echo $moduleWidth; ?>;
		padding: <?php echo $modulePadding; ?>;
		<?php echo ($bgImage != '') ? 'background: url('.$bgImage.') top center no-repeat;' : ''; ?>
		<?php echo ($isBgColor) ? 'background-color: ' . $bgColor : ''; ?>
	}
	#vina-ticker-virtuemart<?php echo $module->id; ?> .vina-item {
		padding: <?php echo $itemPadding; ?>;
		color: <?php echo $itemTextColor; ?>;
		border-bottom: solid 1px <?php echo $bgColor; ?>;
		<?php echo ($isItemBgColor) ? 'background-color: ' . $itemBgColor : ''; ?>
	}
	#vina-ticker-virtuemart<?php echo $module->id; ?> .vina-item a {
		color: <?php echo $itemLinkColor; ?>;
	}
	#vina-ticker-virtuemart<?php echo $module->id; ?> .header-block {
		color: <?php echo $headerColor; ?>;
		margin-bottom: <?php echo $modulePadding; ?>;
	}
	</style>

	<!-- HTML Block -->
	<div id="vina-ticker-virtuemart<?php echo $module->id; ?>" class="vina-ticker-virtuemart">
		<!-- Header Buttons Block -->
		<?php if($headerBlock) : ?>
		<div class="header-block">
			<div class="row-fluid">
				<?php if(!empty($headerText)) : ?>
				<div class="span<?php echo ($controlButtons) ? 8 : 12; ?>">
					<h3><?php echo $headerText; ?></h3>
				</div>
				<?php endif; ?>
				
				<?php if($controlButtons) : ?>
				<div class="span<?php echo empty($headerText) ? 12 : 4; ?>">
					<div class="control-block pull-right">
						<span class="up">UP</span>
						<span class="toggle">TOGGLE</span>
						<span class="down">DOWN</span>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Items Block -->	
		<div class="vina-items-wrapper">
			<div class="vina-items">
				<?php 
					foreach($products as $key => $product) :
						$image  = $product->images[0];
						$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
						$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;
						$pLink  = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id);
						$pName  = $product->product_name;
						$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
						$sDesc  = $product->product_s_desc;
						$pDesc  = (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, 60, ' ...') : '';
						$detail = JHTML::link($pLink, vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), array('title' => JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), 'class' => 'product-details jutooltip'));
						$stock  = $productModel->getStockIndicator($product);
						$sLevel = $stock->stock_level;
						$sTip   = $stock->stock_tip;
						$handle = shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
						$pPrice = shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency));
						$sPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						$dPrice = $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
						
						$isSaleLabel = (!empty($product->prices['salesPriceWithDiscount'])) ? 1 : 0;
				?>
				<div class="vina-item">
					<!-- Image Block -->
					<?php if($productImage && !empty($pImage)) : ?>
					<div class="image-block products-images pull-left">
						<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>">
							<img src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
						</a>
					</div>
					<?php endif; ?>
					
					<!-- Text Block -->
					<div class="text-block">
						<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) { ?>
							<div class="inner-text-block">
						<?php } ?>
						<!-- Product Name -->
						<?php if($productName) : ?>
						<h3 class="product-title"><a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>"><?php echo $pName; ?></a></h3>
						<?php endif; ?>
						
						<!-- Product Description -->
						<?php if($productDesc && !empty($pDesc)) : ?>
						<div class="product-description"><?php echo $pDesc; ?></div>
						<?php endif; ?>
						
						<!-- Product Price -->
						<?php if($productPrice) : ?>
						<div class="product-price vm-prices-block">
							<div class="product-price">																
								<?php echo $pPrice; ?>						
							</div>
						</div>
						<?php endif; ?>
						
						<!-- Product Rating -->
						<?php if($productRating) : ?>
						<div class="product-rating vm-product-rating-container"><?php echo $rating; ?></div>
						<?php endif; ?>
						
						<!-- Product Stock -->
						<?php if($productStock) : ?>
						<div class="product-stock">
							<span class="vmicon vm2-<?php echo $sLevel; ?>" title="<?php echo $sTip; ?>"></span>
							<?php echo $handle; ?>
						</div>
						<?php endif; ?>
						
						<!-- Add to Cart Button & View Details Button -->
						<?php if($addtocart || $viewDetails) : ?>
						<div class="button-group">
							<!-- Product Add To Cart -->
							<?php if($addtocart) : ?>
							<div class="addtocart">
								<ul>
									<li class="jutooltip" title="<?php echo JText::_("VINA_VMART_ADDTOCART_BUTTON"); ?>">										
										<?php modVinaTickerVirtueMartHelper::addtocart($product); ?>
									</li>
								</ul>
							</div>
							<?php endif; ?>
							
							<!-- View Details Button -->
							<?php if($viewDetails) : ?>
							<div class="vm-details-button"><?php echo $detail; ?></div>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) { ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- Javascript Block -->
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#vina-ticker-virtuemart<?php echo $module->id; ?> .vina-items-wrapper').easyTicker({
			direction: 		'<?php echo $direction?>',
			easing: 		'<?php echo $easing?>',
			speed: 			'<?php echo $speed?>',
			interval: 		<?php echo $interval?>,
			height: 		'<?php echo $moduleHeight; ?>',
			visible: 		<?php echo $visible?>,
			mousePause: 	<?php echo $mousePause?>,
			
			<?php if($controlButtons) : ?>
			controls: {
				up: '#vina-ticker-virtuemart<?php echo $module->id; ?> .up',
				down: '#vina-ticker-virtuemart<?php echo $module->id; ?> .down',
				toggle: '#vina-ticker-virtuemart<?php echo $module->id; ?> .toggle',
				playText: 'Play',
				stopText: 'Stop'
			},
			<?php endif; ?>
		});
	});
	</script>
</div>