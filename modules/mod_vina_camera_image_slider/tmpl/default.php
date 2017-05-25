<?php
/*
# ------------------------------------------------------------------------
# Vina Camera Image Slider for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addScript('modules/mod_vina_camera_image_slider/assets/jquery.mobile.customized.min.js', 'text/javascript');
$doc->addScript('modules/mod_vina_camera_image_slider/assets/jquery.easing.1.3.js', 'text/javascript');
$doc->addScript('modules/mod_vina_camera_image_slider/assets/camera.js', 'text/javascript');
$doc->addStyleSheet('modules/mod_vina_camera_image_slider/assets/camera.css');

$timthumb = JURI::base() . 'modules/mod_vina_camera_image_slider/libs/timthumb.php?a=c&amp;q=99&amp;z=0';
?>
<div class="vina-camera-slider-wrapper">
	<!-- style block -->
	<style type="text/css" scoped>
	#vina-camera-slider-wrapper<?php echo $module->id; ?> {
		width: <?php echo $moduleWidth; ?>;
		max-width: <?php echo $maxWidth; ?>;
		clear: both;
	}
	#vina-copyright<?php echo $module->id; ?> {
		font-size: 12px;
		<?php if(!$params->get('copyRightText', 0)) : ?>
		height: 1px;
		overflow: hidden;
		<?php endif; ?>
		clear: both;
	}
	</style>
	
	<!-- slideshow block -->
	<div id="vina-camera-slider-wrapper<?php echo $module->id; ?>" class="vina-camera-slider">
		<div class="camera_wrap <?php echo $moduleStyle; ?>" id="vina-camera-slider<?php echo $module->id; ?>">
			<?php foreach($slides as $slide) : ?>
			<?php
				$image 		= $slide->img;
				$image 		= (strpos($image, 'http://') === false) ? JURI::base() . $image : $image;
				$bigImage   = $image;
				
				if($resizeImage) $bigImage = $timthumb . '&amp;w=' . $imageWidth . '&amp;h=' . $imageHeight . '&amp;src=' . $image;
				
				$thumbImage = $timthumb . '&amp;w=' . $thumbnailWidth . '&amp;h=' . $thumbnailHeight . '&amp;src=' . $image;
			?>
			<div data-thumb="<?php echo $thumbImage; ?>" data-src="<?php echo $bigImage; ?>">
				<?php echo ($displayCaptions) ? $slide->text : ''; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	
	<!-- copyright block -->
	<?php modVinaCameraImageSliderHelper::getCopyrightText($module); ?>
	
	<!-- javascript block -->
	<script type="text/javascript">
	jQuery(document).ready(function ($) {
		jQuery('#vina-camera-slider<?php echo $module->id; ?>').camera({
			loader				: '<?php echo $loaderStyle; ?>',
			barDirection		: '<?php echo $barDirection; ?>',
			barPosition			: '<?php echo $barPosition; ?>',
			fx					: '<?php echo $fx; ?>',
			piePosition			: '<?php echo $piePosition; ?>',
			height				: '<?php echo $moduleHeight; ?>',
			hover				: <?php echo ($pauseHover) ? 'true' : 'false'; ?>,			
			navigation			: <?php echo ($navigation) ? 'true' : 'false'; ?>,
			navigationHover		: <?php echo ($navigationHover) ? 'true' : 'false'; ?>,
			pagination			: <?php echo ($pagination) ? 'true' : 'false'; ?>,
			playPause			: <?php echo ($playPause) ? 'true' : 'false'; ?>,
			pauseOnClick		: <?php echo ($pauseOnClick) ? 'true' : 'false'; ?>,
			thumbnails			: <?php echo ($thumbnails) ? 'true' : 'false'; ?>,
			time				: <?php echo $duration; ?>,
			transPeriod			: <?php echo $transPeriod; ?>,
		});
	});
	</script>
</div>