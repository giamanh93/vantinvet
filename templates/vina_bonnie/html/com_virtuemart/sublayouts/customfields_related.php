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

$product = $viewData['product'];
$position = $viewData['position'];
$customTitle = isset($viewData['customTitle'])? $viewData['customTitle']: false;;
if(isset($viewData['class'])){
	$class = $viewData['class'];
} else {
	$class = 'product-fields';
}

if (!empty($product->customfieldsSorted[$position])) {
	?>
	<div class="<?php echo $class?>">
		<?php
		if($customTitle and isset($product->customfieldsSorted[$position][0])){
			$field = $product->customfieldsSorted[$position][0]; ?>
		<div class="product-fields-title-wrapper">
			<h3 class="product-fields-title vina-header"><span><?php echo vmText::_ ($field->custom_title) ?></span></h3>
			<?php if ($field->custom_tip) {
				echo JHtml::tooltip (vmText::_($field->custom_tip), vmText::_ ($field->custom_title), 'tooltip.png');
			} ?>
		</div> <?php
		}
		?>
		<div id="vina_related_product" class="pro_related">
			<div class="list_carousel responsive">
				<ul id="vina_caroufredsel" class="vmproduct">			
					<?php					
					$custom_title = null;
					foreach ($product->customfieldsSorted[$position] as $field) {
						if ( $field->is_hidden ) //OSP http://forum.virtuemart.net/index.php?topic=99320.0
						continue;
						?>																		
						<?php if (!$customTitle and $field->custom_title != $custom_title and $field->show_title) { ?>
							<span class="product-fields-title-wrapper"><span class="product-fields-title"><strong><?php echo vmText::_ ($field->custom_title) ?></strong></span>
								<?php if ($field->custom_tip) {
									echo JHtml::tooltip ($field->custom_tip, vmText::_ ($field->custom_title), 'tooltip.png');
								} ?></span>
						<?php }
						if (!empty($field->display)){
							?>
							<li class="item">
								<div class="product product-field">
									<div class="product-field-display">
										<?php echo $field->display ?>									
									</div>
								</div>
							</li>
						<?php
						}
						if (!empty($field->custom_desc)){
							?><div class="product-field-desc"><?php echo vmText::_($field->custom_desc) ?></div><?php
						}
						?>							
					
						<?php $custom_title = $field->custom_title;					
					} ?>
				</ul>
				<div class="buttons">
					<a id="prev_FredSel" class="list_prev" href="#"><span>&lt;</span></a>
					<a id="next_FredSel" class="list_next" href="#"><span>&gt;</span></a>
				</div>
			</div> 			
		</div>
	</div>
<?php } ?>