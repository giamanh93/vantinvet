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
$products_per_row = $viewData['products_per_row'];
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

foreach ($viewData['products'] as $type => $products ) {

	// Category and Columns Counter
	$col = 1;
	$nb = 1;
	$row = 1;
	$BrowseTotalProducts = count($products);
	//vmdebug('amount to browse '.$type,$BrowseTotalProducts);
	if(!empty($type)){
		$productTitle = vmText::_('COM_VIRTUEMART_'.$type.'_PRODUCT');
		?>
		<div class="<?php echo $type ?>-view">
		<h4><?php echo $productTitle ?></h4>
		<?php // Start the Output
	}

	$rowHeights = array();
	$rowsHeight = array();
	foreach($products as $product){
		// This is the structure below

		//structure
		if ($col == 1 && $nb > $products_per_row) {

		}

		if ($col == 1) {

		}

		if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		}
		//structure
		//I wonder if we can simplify it using the $row variable

		$priceRows = 0;
		//Lets calculate the height of the prices
		foreach($currency->_priceConfig as $name=>$values){
			if(!empty($currency->_priceConfig[$name][0])){
				if(!empty($product->prices[$name]) or $name == 'billTotal' or $name == 'billTaxAmount'){
					$priceRows++;
				}
			}
		}
		$rowHeights[$row]['price'][] = $priceRows;
		$position = 'addtocart';
		if(!empty($product->customfieldsSorted[$position])){
			$customs = count($product->customfieldsSorted[$position]);
		} else {
			$customs = 0;
		}
		$rowHeights[$row]['customs'][] = $customs;
		$nb ++;

		if ($col == $products_per_row || $nb>$BrowseTotalProducts) {
			$col = 1;
			$max = 0;
			foreach($rowHeights[$row] as $type => $cols){
				//vmdebug('my rows heights $type',$type);
				$rowsHeight[$row][$type] = 0;
				foreach($cols as $col){
					$rowsHeight[$row][$type] =  max($rowsHeight[$row][$type],$col);
					//vmdebug('my rows  $cols',$col);
				}
				//vmdebug('my rows  $type',$type);
			}

			$rowHeights = array();
			$row++;
		} else {
			$col ++;
		}

	}

	//vmdebug('my rows heights',$rowsHeight);
	$col = 1;
	$nb = 1;
	$row = 1;

	foreach ( $products as $product ) {

		// Show the horizontal seperator
		if ($col == 1 && $nb > $products_per_row) { ?>
			<div class="horizontal-separator"></div>
		<?php }

		// this is an indicator wether a row needs to be opened or not
		if ($col == 1) { ?>
			<div class="row">
		<?php }

		// Show the vertical seperator
		if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		}

		// Show Products ?>
	<div class="product vm-product-horizon vm-col<?php echo ' vm-col-' . $products_per_row . $show_vertical_separator ?>">
      <div class="vm-product-media-rating">
        <div class="vm-product-media-container">
					<a title="<?php echo $product->product_name ?>" href="<?php echo $product->link.$ItemidStr; ?>">
					<?php
						echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false);
					?>
					</a>
				</div>
        <div class="clear"></div>
        <div class="vm-rating-show-stars">
          <?php
            echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$showRating, 'product'=>$product));
            if ( VmConfig::get ('display_stock', 1)) { ?>
        </div>
        <div class="clear"></div>
        <div class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></div>
          <?php } ?>
          <?php //echo shopFunctionsF::renderVmField('stockhandle',array('product'=>$product));
          ?>
        <div class="clear"></div>
        <h2><?php echo JHtml::link ($product->link.$ItemidStr, $product->product_name); ?></h2>
      </div>
		<?php vmdebug('my number of rows for the prices rows heights',$rowsHeight[$row]); ?>
      <div class="vm-product-details-container">
        <?php // Product Short Description
          if (!empty($product->product_s_desc)) {
            ?>
            <p class="product_s_desc">
              <?php echo shopFunctionsF::limitStringByWord ($product->product_s_desc, 40, '...') ?>
            </p>
        <?php } ?>
        <div class="vm3pr-<?php echo $rowsHeight[$row]['price'] ?>">
          <?php echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
        </div>
        <?php echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'row'=>0)); ?>

        <div class="vm-details-button">
          <?php // Product Details Button
          echo JHtml::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id , FALSE), vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $product->product_name, 'class' => 'product-details' ) );
          ?>
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

	if(!empty($type)){
		// Do we need a final closing row tag?
		//if ($col != 1) {
		?>
			<div class="clear"></div>
			</div>
		<?php
		//}
	}
}
