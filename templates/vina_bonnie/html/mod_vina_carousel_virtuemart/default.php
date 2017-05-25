<?php
/*
# ------------------------------------------------------------------------
# Vina Product Carousel for VirtueMart for Joomla 3
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
JHtml::_('behavior.modal');
$doc = JFactory::getDocument();
$doc->addScript('modules/' . $module->module . '/assets/js/owl.carousel.js', 'text/javascript');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.carousel.css');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/owl.theme.css');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/custom.css');

// Get New Products
$db     = JFactory::getDBO();
$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products ORDER BY virtuemart_product_id DESC LIMIT 0, 10";
$db->setQuery($query);
$newIds = $db->loadColumn();
?>
<style type="text/css" scoped>
#vina-carousel-virtuemart<?php echo $module->id; ?> {
	width: <?php echo $moduleWidth; ?>;
	height: <?php echo $moduleHeight; ?>;
	margin: <?php echo $moduleMargin; ?>;
	padding: <?php echo $modulePadding; ?>;
	<?php echo ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : ''; ?>
	<?php echo ($isBgColor) ? "background-color: {$bgColor};" : '';?>
	overflow: hidden;
}
#vina-carousel-virtuemart<?php echo $module->id; ?> .item {
	<?php echo ($isItemBgColor) ? "background-color: {$itemBgColor};" : ""; ?>;
	color: <?php echo $itemTextColor; ?>;
	padding: <?php echo $itemPadding; ?>;
	margin: <?php echo $itemMargin; ?>;
}
#vina-carousel-virtuemart<?php echo $module->id; ?> .item a {
	color: <?php echo $itemLinkColor; ?>;
}
</style>
<?php 
	$ratingModel = VmModel::getModel('ratings');
	$ItemidStr = '';
	$Itemid = shopFunctionsF::getLastVisitedItemId();
	if(!empty($Itemid)){
		$ItemidStr = '&Itemid='.$Itemid;
	}
?>
<div id="vina-carousel-virtuemart<?php echo $module->id; ?>" class="vina-carousel-virtuemart owl-carousel">
	<?php 
		foreach($products as $key => $product) :
			$image  = $product->images[0];
			$image_second = $product->images[1];
			$pImage = (!empty($image)) ? JURI::base() . $image->file_url : '';
			$pImage = (!empty($pImage) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage : $pImage;			
			if(!empty($image_second)) $pImage_second = JURI::base() . $image_second->file_url;
			$pImage_second = (!empty($pImage_second) && $resizeImage) ? $timthumb . '&amp;src=' . $pImage_second : $pImage_second;
			
			$pLink  = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id);
			$pName  = $product->product_name;			
			$rating = shopFunctionsF::renderVmSubLayout('rating', array('showRating' => $productRating, 'product' => $product));
			$sDesc  = $product->product_s_desc;
			$pDesc  = (!empty($sDesc)) ? shopFunctionsF::limitStringByWord($sDesc, 60, ' ...') : '';
			$detail = JHTML::link($pLink, JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), array('title' => JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), 'class' => 'product-details jutooltip'));
			$stock  = $productModel->getStockIndicator($product);
			$sLevel = $stock->stock_level;
			$sTip   = $stock->stock_tip;
			$handle = shopFunctionsF::renderVmSubLayout('stockhandle', array('product' => $product));
			$pPrice = shopFunctionsF::renderVmSubLayout('prices', array('product' => $product, 'currency' => $currency));			
			$dPrice = $currency->createPriceDiv('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$salesPrice = $currency->createPriceDiv('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			$basePriceWithTax = $currency->createPriceDiv('basePriceWithTax', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
			// Show Label Sale Or New
			$isSaleLabel = (!empty($product->prices['salesPriceWithDiscount'])) ? 1 : 0;
			
			$pid = $product->virtuemart_product_id;
			$isNewLabel = in_array($pid, $newIds);
	?>
	<div class="item product vm-col">		
		<!-- Image Block -->		
		<?php if($productImage && !empty($pImage)) : ?>
		<div class="product-status">
			<!-- Check Product Label -->
			<?php if($isSaleLabel == 1) : ?>
				<div class="label-pro status-sale"><span><?php echo JTEXT::_('VINA_VIRTUEMART_LABEL_SALE'); ?></span></div>
			<?php endif; ?>
			<?php if($isNewLabel && $isSaleLabel == 1) : ?>
			<div class="label-pro status-new"><span><?php echo JTEXT::_('VINA_VIRTUEMART_LABEL_NEW'); ?></span></div>
			<?php endif; ?>
			<?php if($isNewLabel && $isSaleLabel == 0) : ?>
			<div class="label-pro status-new"><span><?php echo JTEXT::_('VINA_VIRTUEMART_LABEL_NEW'); ?></span></div>
			<?php endif; ?>
		</div>
		<div class="vm-product-media-container image-block">
			<?php if(!empty($image_second)) { ?>
				<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>" class="double-image">
					<div class="pro-image first-image">
						<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
					</div>
					<div class="pro-image second-image">
						<img class="browseProductImage" src="<?php echo $pImage_second; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />				
					</div>				
				</a>
			<?php }else { ?>
				<a href="<?php echo $pLink; ?>" title="<?php echo $pName; ?>" class="single-image">
					<div class="pro-image">
						<img class="browseProductImage" src="<?php echo $pImage; ?>" alt="<?php echo $pName; ?>" title="<?php echo $pName; ?>" />
					</div>										
				</a>						
			<?php } ?>
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
			
			<!-- Product Rating -->
			<?php //if($productRating) : ?>
			<!--<div class="product-rating"><?php //echo $rating; ?></div>-->
			<?php //endif; ?>
			<!-- Product Rating -->
			<?php if ($productRating) { ?>
				<div class="vm-product-rating-container">
				<?php
					$maxrating = VmConfig::get('vm_maximum_rating_scale',5);
					$rating = $ratingModel->getRatingByProduct($product->virtuemart_product_id);
					$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);
					if(empty($rating->rating)) { ?>						
						<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
						</div>
					<?php } else {						
						$ratingwidth = $rating->rating * 14; ?>
						<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($rating->rating) . '/' . $maxrating) ?>" class="ratingbox" >
						  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
						</div>
					<?php } ?> 
					<?php if(!empty($reviews)) {					
						$count_review = 0;
						foreach($reviews as $k=>$review) {
							$count_review ++;
						}										
					?>
						<span class="amount">
							<a href="<?php echo $pLink; ?>" target="_blank" ><?php echo $count_review.' '.JText::_('VINA_VIRTUEMART_REVIEW');?></a>
						</span>
					<?php } ?>
				</div>
           	<?php } ?>
			<!-- Product Stock -->
			<?php if($productStock) : ?>
			<div class="product-stock">
				<span class="vmicon vm2-<?php echo $sLevel; ?>" title="<?php echo $sTip; ?>"></span>
				<?php echo $handle; ?>
			</div>
			<?php endif; ?>					
			
			<!-- Product Description -->
			<?php if($productDesc && !empty($pDesc)) : ?>
			<div class="product-description"><?php echo $pDesc; ?></div>
			<?php endif; ?>
			
			<!-- Product Price -->
			<?php if($productPrice) : ?>
			<div class="vm-prices-block">
				<div class="product-price">														
					<?php echo $pPrice; ?>																								
				</div>
			</div>
			<?php endif; ?>			
			
			<!-- Add to Cart Button & View Details Button -->
			<?php if($addtocart || $viewDetails) : ?>
			<div class="button-group actions">
				<!-- Product Add To Cart -->
				<?php if($addtocart) : ?>				
				<div class="vm-addtocart-button addtocart">					
					<ul>
						<li class="jutooltip" title="<?php echo JText::_("VINA_VMART_ADDTOCART_BUTTON"); ?>">							
							<?php modVinaCarouselVirtueMartHelper::addtocart($product); ?>
						</li>
					</ul>
				</div>
				<?php endif; ?>							
				
				<?php //Wishlist and Details					
				if(is_dir(JPATH_BASE."/components/com_wishlist/")) {
					$app = JFactory::getApplication();
				?>
					<div class="vm-icons-block">							
						<div class="vm-details-icon">
							<?php // Product Details Icon							
							echo JHtml::link($pLink,'<i class="icon icon-eye-open"></i><span>'.JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS').'</span>', array ('title' => JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), 'class' => 'icon-details jutooltip' ) );						
							?>
						</div>
						<div class="vm-wishlist">							
							<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
						</div>
					</div>
				<?php } else { ?>					
					<!-- View Details Button -->
					<?php if($viewDetails) : ?>
					<div class="vm-details-button"><?php echo $detail; ?></div>
					<?php endif; ?>					
				<?php } ?>
			</div>
			<?php endif; ?>
		<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) { ?>
			</div>
		<?php } ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#vina-carousel-virtuemart<?php echo $module->id; ?>").owlCarousel({
		items : 			<?php echo $itemsVisible; ?>,
        itemsDesktop : 		<?php echo $itemsDesktop; ?>,
        itemsDesktopSmall : <?php echo $itemsDesktopSmall; ?>,
        itemsTablet : 		<?php echo $itemsTablet; ?>,
        itemsTabletSmall : 	<?php echo $itemsTabletSmall; ?>,
        itemsMobile : 		<?php echo $itemsMobile; ?>,
        singleItem : 		<?php echo ($singleItem) ? 'true' : 'false'; ?>,
        itemsScaleUp : 		<?php echo ($itemsScaleUp) ? 'true' : 'false'; ?>,

        slideSpeed : 		<?php echo $slideSpeed; ?>,
        paginationSpeed : 	<?php echo $paginationSpeed; ?>,
        rewindSpeed : 		<?php echo $rewindSpeed; ?>,

        autoPlay : 		<?php echo $autoPlay; ?>,
        stopOnHover : 	<?php echo ($stopOnHover) ? 'true' : 'false'; ?>,

        navigation : 	<?php echo ($navigation) ? 'true' : 'false'; ?>,
        rewindNav : 	<?php echo ($rewindNav) ? 'true' : 'false'; ?>,
        scrollPerPage : <?php echo ($scrollPerPage) ? 'true' : 'false'; ?>,

        pagination : 		<?php echo ($pagination) ? 'true' : 'false'; ?>,
        paginationNumbers : <?php echo ($paginationNumbers) ? 'true' : 'false'; ?>,

        responsive : 	<?php echo ($responsive) ? 'true' : 'false'; ?>,
        autoHeight : 	<?php echo ($autoHeight) ? 'true' : 'false'; ?>,
        mouseDrag : 	<?php echo ($mouseDrag) ? 'true' : 'false'; ?>,
        touchDrag : 	<?php echo ($touchDrag) ? 'true' : 'false'; ?>,
	});
}); 
</script>