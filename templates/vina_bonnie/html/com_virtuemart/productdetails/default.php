<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Eugen Stranz, Max Galt
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 8610 2014-12-02 18:53:19Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Let's see if we found the product */
if (empty($this->product)) {
	echo vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
	echo '<br /><br />  ' . $this->continue_link_html;
	return;
}

echo shopFunctionsF::renderVmSubLayout('askrecomjs',array('product'=>$this->product));

vmJsApi::jDynUpdate();
vmJsApi::addJScript('updDynamicListeners',"
jQuery(document).ready(function() { // GALT: Start listening for dynamic content update.
	// If template is aware of dynamic update and provided a variable let's
	// set-up the event listeners.
	if (Virtuemart.container)
		Virtuemart.updateDynamicUpdateListeners();

}); ");

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	jQuery(function($) {							
		$('.to_review, .count_review').click(function() {
			$('html, body').animate({
				scrollTop: ($('#tab-block').offset().top - 120)
			},500);									
			$('#vinaTab li').removeClass('active');
			$('#vinaTab li.tab_review').addClass('active');
			$('#vinaTabContent >div').removeClass('active');
			$('#vinaTabContent #vina-reviews').addClass('active in');
		});
	})
");

if(vRequest::getInt('print',false)){ ?>
<body onload="javascript:print();">
<?php } ?>

<div class="productdetails-view productdetails">
    <?php
    // Product Navigation
    if (VmConfig::get('product_navigation', 1)) {
	?>
        <div class="product-neighbours">
	    <?php
	    if (!empty($this->product->neighbours ['previous'][0])) {
		$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['previous'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $prev_link, $this->product->neighbours ['previous'][0]
			['product_name'], array('rel'=>'prev', 'class' => 'previous-page','data-dynamic-update' => '1'));
	    }
	    if (!empty($this->product->neighbours ['next'][0])) {
		$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $this->product->neighbours ['next'][0] ['virtuemart_product_id'] . '&virtuemart_category_id=' . $this->product->virtuemart_category_id, FALSE);
		echo JHtml::_('link', $next_link, $this->product->neighbours ['next'][0] ['product_name'], array('rel'=>'next','class' => 'next-page','data-dynamic-update' => '1'));
	    }
	    ?>
    	<div class="clear"></div>
        </div>
    <?php } // Product Navigation END
    ?>

    <div class="vm-product-container">
		<div class="vm-product-media-container">
			<?php echo $this->loadTemplate('images'); ?>
			
			<?php //Additional Images ?>
			<?php
			$count_images = count ($this->product->images);
			if ($count_images > 1) {
				echo $this->loadTemplate('images_additional');
			}
			// event onContentBeforeDisplay
			echo $this->product->event->beforeDisplayContent;
			?>		
		</div>

		<div class="vm-product-details-container">
			<div class="vm-product-details-inner spacer-buy-area">
				<?php // Back To Category Button ?>				
				<?php
				if ($this->product->virtuemart_category_id) {
					$catURL =  JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE);
					$categoryName = $this->product->category_name ;
				} else {
					$catURL =  JRoute::_('index.php?option=com_virtuemart');
					$categoryName = vmText::_('COM_VIRTUEMART_SHOP_HOME') ;
				}
				?>				
				<div class="back-to-category">
					<a href="<?php echo $catURL ?>" class="product-details" title="<?php echo $categoryName ?>"><?php echo vmText::sprintf('COM_VIRTUEMART_CATEGORY_BACK_TO',$categoryName) ?></a>
				</div>

				<?php // Product Title   ?>
				<div class="product-name">
						<h1><?php echo $this->product->product_name ?></h1>
				</div>
								
				<?php // afterDisplayTitle Event ?>
				<?php echo $this->product->event->afterDisplayTitle ?>
			
				<?php // Show Rating ?>
				<div class="vm-product-rating-container">
					<?php echo shopFunctionsF::renderVmSubLayout('rating',array('showRating'=>$this->showRating,'product'=>$this->product)); ?>									
					<span class="separator">|</span>
					<span class="add_review"><a href="javascript:void(0)" class="to_review"><?php echo JText::_('VINA_ADD_YOUR_REVIEW'); ?></a></span>
				</div>

				<?php // Product Edit Link ?>
				<?php echo $this->edit_link; ?>	
				
				<?php // Manufacturer of the Product ?>
				<?php if (VmConfig::get('show_manufacturers', 1) && !empty($this->product->virtuemart_manufacturer_id)) { ?>					
					<?php echo $this->loadTemplate('manufacturer'); ?>					
				<?php } ?>
				<?php
					echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'ontop'));
				?>
				<?php // Price Block ?>
				<div class="spacer-buy-area">
					<?php 
						// TODO in Multi-Vendor not needed at the moment and just would lead to confusion
						/* $link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&virtuemart_vendor_id='.$this->product->virtuemart_vendor_id);
						  $text = vmText::_('COM_VIRTUEMART_VENDOR_FORM_INFO_LBL');
						  echo '<span class="bold">'. vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_VENDOR_LBL'). '</span>'; ?><a class="modal" href="<?php echo $link ?>"><?php echo $text ?></a><br />
						*/
					?>
					<div class="vm-prices-block">
					<?php			
					/*if (is_array($this->productDisplayShipments)) {
						foreach ($this->productDisplayShipments as $productDisplayShipment) {
							echo $productDisplayShipment;
						}
					}
					if (is_array($this->productDisplayPayments)) {
						foreach ($this->productDisplayPayments as $productDisplayPayment) {
							echo $productDisplayPayment;
						}
					}*/
					//In case you are not happy using everywhere the same price display fromat, just create your own layout
					//in override /html/fields and use as first parameter the name of your file										
					echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$this->product,'currency'=>$this->currency));							
					?>
					</div>
				</div>							
				
				<?php // Product Short Description ?>
				<?php
				if (!empty($this->product->product_s_desc)) {
				?>
					<div class="product-short-description">
					<?php
					/** @todo Test if content plugins modify the product description */
					echo nl2br($this->product->product_s_desc);
					?>
					</div>
				<?php } // END Product Short Description?>
				
				<?php // PDF - Print - Email Icon ?>
				<?php
				if (VmConfig::get('show_emailfriend') || VmConfig::get('show_printicon') || VmConfig::get('pdf_icon')) {
				?>
					<div class="icons">
					<?php
					$link = 'index.php?tmpl=component&amp;option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $this->product->virtuemart_product_id;

					//echo $this->linkIcon($link . '&amp;format=pdf', 'COM_VIRTUEMART_PDF', 'pdf_button', 'pdf_icon', false);					
					//echo $this->linkIcon($link . '&amp;print=1', 'COM_VIRTUEMART_PRINT', 'printButton', 'show_printicon',false,true,false,'class="printModal"');					
					$MailLink = 'index.php?option=com_virtuemart&amp;view=productdetails&amp;task=recommend&amp;virtuemart_product_id=' . $this->product->virtuemart_product_id . '&amp;virtuemart_category_id=' . $this->product->virtuemart_category_id . '&amp;tmpl=component';
					//echo $this->linkIcon($MailLink, 'COM_VIRTUEMART_EMAIL', 'emailButton', 'show_emailfriend', false,true,false,'class="recommened-to-friend"');
					?>
					<a class="print printModal icon-print" href="<?php echo $link . '&amp;print=1' ?>" data-original-title="<?php echo JText::_('VINA_VIRTUEMART_PRINT_TITLE'); ?>">
						<span><?php echo vmText::_('COM_VIRTUEMART_PRINT'); ?></span>
					</a>										
					<a class="email-friend recommened-to-friend icon-envelope" href="<?php echo $MailLink; ?>" data-original-title="<?php echo JText::_('VINA_VIRTUEMART_EMAIL_TITLE'); ?>">
						<span><?php echo vmText::_('COM_VIRTUEMART_EMAIL'); ?></span>
					</a>
					<?php
					// Ask a question about this product
					if (VmConfig::get('ask_question', 0) == 1) {
						$askquestion_url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&task=askquestion&virtuemart_product_id=' . $this->product->virtuemart_product_id . '&virtuemart_category_id=' . $this->product->virtuemart_category_id . '&tmpl=component', FALSE);
					?>						
						<a class="ask-a-question icon-send" href="<?php echo $askquestion_url ?>" data-original-title="<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL'); ?>" ><span><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_ENQUIRY_LBL'); ?></span></a>					
					<?php } ?>
					<div class="clear"></div>
					</div>
				<?php } ?>						
				
				<?php // Prices + Add to cart Button					
					echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$this->product));
					echo shopFunctionsF::renderVmSubLayout('stockhandle',array('product'=>$this->product));	
				?>
				<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/") || is_dir(JPATH_BASE . "/components/com_virtuemartproductcompare/") ) {?>
				<div class="add-to-box">
					<!-- Add Compare Button -->
					<ul class="vm-compare">
						<li class="jutooltip" title="<?php echo JText::_('ADD_TO_COMPARE');?>">
							<span class="vm-btn-compare"></span>												
						</li>
					</ul>
					
					<!-- Add Wishlist Button -->
					<?php if(is_dir(JPATH_BASE . "/components/com_wishlist/")) : 
						$app = JFactory::getApplication();	
					?>												
						<div class="btn-wishlist">									
							<?php require(JPATH_BASE . "/templates/".$app->getTemplate()."/html/wishlist.php"); ?>									
						</div>							
					<?php endif; ?>
				</div>
				<?php } ?>
				<!-- Social Button -->																	
				<div class="link-share">
					<!-- AddThis Button BEGIN -->
					<div class="addthis_default_style">
						<a class="addthis_button_compact at300m" href="#">Share</a> 
						<a class="addthis_button_email at300b" target="_blank" title="Email" href="#" tabindex="1000"></a>
						<a class="addthis_button_print at300b" title="In" href="#"></a> 
						<a class="addthis_button_facebook at300b" title="Facebook" href="#"></a> 
						<a class="addthis_button_twitter at300b" title="Tweet" href="#"></a>
					</div>					
					<!-- AddThis Button END --> 
				</div>	
				<!-- End Social Button -->
			</div>					
		</div>
	<div class="clear"></div>	
    </div>
	
	<!-- Tabs Full Description + Review + comment -->
	<div id="tab-block" class="tab-block">
		<ul class="nav nav-pills" id="vinaTab">
			<?php if (!empty($this->product->product_desc)) {?>
			<li class="tab_des active">
				<a data-toggle="tab" href="#vina-description"><?php echo JText::_('VINA_JSHOP_FULL_DESCRIPTION'); ?></a>
			</li>
			<?php }?>			
			<li class="tab_review last"><a data-toggle="tab" href="#vina-reviews"><?php echo JText::_('VINA_JSHOP_OVERVIEWS'); ?></a></li>			
		</ul>
		<div id="vinaTabContent" class="tab-content">			
			<?php // Product Description
			if (!empty($this->product->product_desc)) { ?>
				<div id="vina-description" class="tab-pane fade in product-description active">
					<?php /** @todo Test if content plugins modify the product description */ ?>
					<span class="title"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DESC_TITLE') ?></span>
					<?php echo $this->product->product_desc; ?>
				</div>
			<?php } // Product Description END ?>			
			<div id="vina-reviews" class="tab-pane fade product-review">
				<?php
					echo $this->loadTemplate('reviews');
				?>
			</div>			
		</div>
	</div>
	<?php	
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'normal'));

    // Product Packaging
    $product_packaging = '';
    if ($this->product->product_box) {
	?>
        <div class="product-box">
	    <?php
	        echo vmText::_('COM_VIRTUEMART_PRODUCT_UNITS_IN_BOX') .$this->product->product_box;
	    ?>
        </div>
    <?php } // Product Packaging END ?>

    <?php 
	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'onbot'));

    echo shopFunctionsF::renderVmSubLayout('customfields_related',array('product'=>$this->product,'position'=>'related_products','class'=> 'product-related-products module','customTitle' => true ));

	echo shopFunctionsF::renderVmSubLayout('customfields',array('product'=>$this->product,'position'=>'related_categories','class'=> 'product-related-categories'));

	?>

<?php // onContentAfterDisplay event
echo $this->product->event->afterDisplayContent; ?>

<?php // Show child categories
    if (VmConfig::get('showCategory', 1)) {
		echo $this->loadTemplate('showcategory');
    }?>
	<?php
	echo vmJsApi::writeJS();
	?>

</div>
<?php 
// Zoom Image add code --------------------------------------------------------------------------
$document = JFactory::getDocument();
$app 	  = JFactory::getApplication();
$template = $app->getTemplate();
$document->addScript(JURI::base() . 'templates/' . $template . '/js/jquery.carouFredSel-6.1.0-packed.js');
?>
<script>
	// GALT
	/*
	 * Notice for Template Developers!
	 * Templates must set a Virtuemart.container variable as it takes part in
	 * dynamic content update.
	 * This variable points to a topmost element that holds other content.
	 */
	// If this <script> block goes right after the element itself there is no
	// need in ready() handler, which is much better.
	//jQuery(document).ready(function() {
	Virtuemart.container = jQuery('.productdetails-view');
	Virtuemart.containerSelector = '.productdetails-view';
	//Virtuemart.container = jQuery('.main');
	//Virtuemart.containerSelector = '.main';
	//});

/* FC - Related Products */
var isIE8 = jQuery.browser.msie && + jQuery.browser.version === 8;
if (isIE8 ) {
	function sliderInit6() {							   	
		jQuery('#vina_caroufredsel').carouFredSel({
			auto: false,
			circular: false,
			infinite: true,
			prev: '.buttons #prev_FredSel',
			next: '.buttons #next_FredSel',
			mousewheel: false,
			height: null,
			swipe: {
				onMouse: true,
				onTouch: false
			},				   
			responsive: true,
			width: '100%',
			scroll: 1,
			items: {
				width: 270,
				height: null,	//	optionally resize item-height
				visible: {
					min: 1,
					max: 3
				}
			}
		});
	}
} else {
	function sliderInit6() {							   	
		jQuery('#vina_caroufredsel').carouFredSel({
			auto :false , 
			circular: false,
			infinite: true,
			prev: '.buttons #prev_FredSel',
			next: '.buttons #next_FredSel',
			mousewheel: false,
			swipe: {
				onMouse: true,
				onTouch: false
			},				   
			responsive: true,
			width: '100%',
			height: null,
			scroll: 1,
			items: {
				width: 270,
				height: null,	//	optionally resize item-height
				visible: {
					min: 1,
					max: 3
				}
			}
		});	
	}
}

/* Related Products */
sliderInit6();
</script>
<script type="text/javascript" src="//s7.addthis.com/js/250/addthis_widget.js"></script>