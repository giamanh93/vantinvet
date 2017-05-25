<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2014-02-26 17:07:20Z Milbo $
 */
defined('_JEXEC') or die('Restricted access');
$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}
// Get New Products
$db     = JFactory::getDBO();
$query  = "SELECT virtuemart_product_id FROM #__virtuemart_products ORDER BY virtuemart_product_id DESC LIMIT 0, 10";
$db->setQuery($query);
$newIds = $db->loadColumn();

$productModel = VmModel::getModel('product');
foreach ($viewData['products'] as $type => $products ) {
	$productModel->addImages($products,2);
	$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);
	if(!empty($type) and count($products)>0){
		$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
		<div class="<?php echo $type ?>-view">
			<h4><?php echo $productTitle ?></h4>
			<?php // Start the Output
    }
	// Calculating Products Per Row
	$cellwidth = ' width'.floor ( 100 / $products_per_row );
	$BrowseTotalProducts = count($products);
	$col = 1;
	$nb = 1;
	$row = 1;
	foreach ( $products as $product ) { ?>
		<!-- Show the horizontal seperator -->
		<!-- <?php if ($col == 1 && $nb > $products_per_row) { ?>
			<div class="horizontal-separator"></div>
		<?php } ?> -->
		
		<!-- this is an indicator wether a row needs to be opened or not -->
		<?php if ($col == 1) { ?>
			<div class="row-fluid"> 
		<?php } ?>
		
		<!-- Show the vertical seperator -->
		<?php if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		} ?>
		<?php 
			// Show Label Sale Or New
			$isSaleLabel = (!empty($product->prices['salesPriceWithDiscount'])) ? 1 : 0;
			
			$pid = $product->virtuemart_product_id;
			$isNewLabel = in_array($pid, $newIds);
		?>
		<!-- Show Products -->
		<div class="product vm-col<?php echo ' span' . 12/$products_per_row . $show_vertical_separator ?>">
			<div class="spacer">				
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
				<!-- Image Block -->
				<div class="vm-product-media-container image-block">										
					<?php
						$image = $product->images[0]->displayMediaThumb('class="browseProductImage"', false);
						if(!empty($product->images[1])){
							$image2 = $product->images[1]->displayMediaThumb('class="browseProductImage"', false);
							echo JHTML::_('link', $product->link.$ItemidStr,'<div class="pro-image first-image">'.$image.'</div><div class="pro-image second-image">'.$image2.'</div>',array('class'=>"double-image",'title'=>$product->product_name));
						} else {								
							echo JHTML::_('link', $product->link.$ItemidStr,'<div class="pro-image">'.$image.'</div>',array('class'=>"single-image",'title'=>$product->product_name));
						}
						
					?>																
				</div>	
				
				<!-- Text Block -->
				<div class="text-block">
					<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) { ?>
						<div class="inner-text-block">
					<?php } ?>
					<!-- Product Title + Description Block -->
					<div class="vm-product-descr-container-<?php echo $rowsHeight[$row]['product_s_desc'] ?>">
						<!-- //Product Title -->
						<h2 class="product-title"><?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?></h2>
						
						<!-- //Product Short Description -->
						<?php if(!empty($rowsHeight[$row]['product_s_desc'])){ ?>						
							<!--<p class="product_s_desc">							
								<?php if (!empty($product->product_s_desc)) {
									echo shopFunctionsF::limitStringByWord ($product->product_s_desc, 60, ' ...');
								 } ?>
							</p> -->
						<?php } ?>
					</div>
					
					<!-- Rating Block -->
					<div class="vm-product-rating-container">
						<?php 
							echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product));						
							if ( VmConfig::get ('display_stock', 1)) { ?>
								<span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span>
							<?php }
							echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$product));
						?>
					</div>
					
					<?php //echo $rowsHeight[$row]['price'] ?>
					<div class="vm-prices-block vm3pr-<?php echo $rowsHeight[$row]['price'] ?>">
						<?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
					</div>
					<div class="clear"></div>
					
					<div class="actions">
						<?php //echo $rowsHeight[$row]['customs'] ?>
						<div class="vm-addtocart-button vm3pr-<?php echo $rowsHeight[$row]['customfields'] ?>"> 
							<?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
						</div>					
						<?php //Wishlist and Details				
						if(is_dir(JPATH_BASE."/components/com_wishlist/")) {
							$app = JFactory::getApplication();
						?>
							<div class="vm-icons-block">							
								<div class="vm-details-icon">
									<?php // Product Details Icon
									$link = empty($product->link)? $product->canonical:$product->link;
									echo JHtml::link($link.$ItemidStr,'<i class="icon icon-eye-open"></i><span>'.JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS').'</span>', array ('title' => JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), 'class' => 'icon-details jutooltip' ) );						
									?>
								</div>
								<div class="vm-wishlist">							
									<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>													
								</div>
							</div>
						<?php } else { ?>
							<div class="vm-details-button">
								<?php // Product Details Button
								$link = empty($product->link)? $product->canonical:$product->link;
								echo JHtml::link($link.$ItemidStr,JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), array ('title' => JText::_('VINA_VIRTUEMART_PRODUCT_DETAILS'), 'class' => 'product-details jutooltip' ) );								
								?>
							</div>						
						<?php } ?>
					</div>
					<?php if(is_dir(JPATH_BASE."/components/com_wishlist/")) { ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php
    $nb ++;

      // Do we need to close the current row now?
      if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>
    <div class="clear"></div>
  </div>
      <?php
      	$col = 1;
		$row++;
    } else {
      $col ++;
    }
  }

      if(!empty($type)and count($products)>0){
        // Do we need a final closing row tag?
        //if ($col != 1) {
      ?>
    <div class="clear"></div>
  </div>
    <?php
    // }
    }
  }
