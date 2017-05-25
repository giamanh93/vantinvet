<?php defined('_JEXEC') or die('Restricted access');

$product = $viewData['product'];
$ratingModel = VmModel::getModel('ratings');
$reviews = $ratingModel->getReviewsByProduct($product->virtuemart_product_id);

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

if ($viewData['showRating']) { 
	$maxrating = VmConfig::get('vm_maximum_rating_scale', 5);
	if (empty($product->rating)) {
	?>
		<div class="ratingbox dummy" title="<?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?>" >
		</div>
	<?php
	} else {
		$ratingwidth = $product->rating * 14;		
	?>
		<div title=" <?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($product->rating) . '/' . $maxrating) ?>" class="ratingbox" >
		  <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
		</div>
	<?php } 
	if(!empty($reviews)) {					
		$count_review = 0;
		foreach($reviews as $k=>$review) {
			$count_review ++;
		} ?>
		<span class="amount">
			<?php echo JHtml::link($product->link.$ItemidStr, $count_review.' '.JText::_('VINA_VIRTUEMART_REVIEW'),'target = "_blank"'); ?>			
		</span>
	<?php } ?>	
<?php }