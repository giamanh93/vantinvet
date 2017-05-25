<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_images.php 8508 2014-10-22 18:57:14Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
if(VmConfig::get('usefancy',1)){
	vmJsApi::addJScript( 'fancybox/jquery.fancybox-1.3.4.pack', false);
	vmJsApi::css('jquery.fancybox-1.3.4');
	$document = JFactory::getDocument ();
	$imageJS = '
	jQuery(document).ready(function() {
		Virtuemart.updateImageEventListeners()
	});
	Virtuemart.updateImageEventListeners = function() {
		jQuery("a[data-rel=vm-additional-images]").fancybox({
			"titlePosition" 	: "inside",
			"transitionIn"	:	"elastic",
			"transitionOut"	:	"elastic"
		});
		jQuery(".additional-images a.product-image.image-0").removeAttr("data-rel");
		jQuery(".additional-images img.product-image").click(function() {
			jQuery(".additional-images a.product-image").attr("data-rel","vm-additional-images" );
			jQuery(this).parent().children("a.product-image").removeAttr("data-rel");
			var src = jQuery(this).parent().children("a.product-image").attr("href");
			jQuery(".main-image img").attr("src",src);
			jQuery(".main-image img").attr("alt",this.alt );
			jQuery(".main-image a").attr("href",src );
			jQuery(".main-image a").attr("title",this.alt );
			jQuery(".main-image .vm-img-desc").html(this.alt);
			
			/* Zoom Image add code */
			jQuery(".zoomContainer").remove();
			jQuery("#zoom-image").elevateZoom();
			/* Zoom Image end */
		});		
	}
	';
} else {
	vmJsApi::addJScript( 'facebox',false );
	vmJsApi::css( 'facebox' );
	$document = JFactory::getDocument ();
	$imageJS = '
	jQuery(document).ready(function() {
		Virtuemart.updateImageEventListeners()
	});
	Virtuemart.updateImageEventListeners = function() {
		jQuery("a[data-rel=vm-additional-images]").facebox();
		var imgtitle = jQuery("span.vm-img-desc").text();
		jQuery("#facebox span").html(imgtitle);
	}
	';
}
vmJsApi::addJScript('imagepopup',$imageJS);

// Zoom Image add code --------------------------------------------------------------------------
$document = JFactory::getDocument();
$app 	  = JFactory::getApplication();
$template = $app->getTemplate();
$document->addScript(JURI::base() . 'templates/' . $template . '/js/jquery.elevatezoom.js');

$zoomJs = 'jQuery(document).ready(function() {
	jQuery("#zoom-image").elevateZoom();	
});';

$document->addScriptDeclaration($zoomJs);
// Zoom Image end ---------------------------------------------------------------------------------

if (!empty($this->product->images)) {
	$image = $this->product->images[0];
?>
	<div class="main-image">
		<?php echo $image->displayMediaFull('id="zoom-image"',true,"data-rel='vm-additional-images'"); ?>
		<?php //echo $this->product->images[0]->displayMediaFull('class="medium-image" id="medium-image"', true, "rel='vm-additional-images'"); ?>				
	</div>
<?php } ?>