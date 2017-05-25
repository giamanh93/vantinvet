<?php
/*
# ------------------------------------------------------------------------
# Vina Articles Carousel for Joomla 3
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
$doc->addScript('modules/mod_vina_carousel_content/assets/js/owl.carousel.js', 'text/javascript');
$doc->addStyleSheet('modules/mod_vina_carousel_content/assets/css/owl.carousel.css');
$doc->addStyleSheet('modules/mod_vina_carousel_content/assets/css/owl.theme.css');
?>
<style type="text/css" scoped>
#vina-carousel-content<?php echo $module->id; ?> {
	width: <?php echo $moduleWidth; ?>;
	height: <?php echo $moduleHeight; ?>;
	margin: <?php echo $moduleMargin; ?>;
	padding: <?php echo $modulePadding; ?>;
	<?php echo ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : ''; ?>
	<?php echo ($isBgColor) ? "background-color: {$bgColor};" : '';?>
	overflow: hidden;
}
#vina-carousel-content<?php echo $module->id; ?> .item {
	<?php echo ($isItemBgColor) ? "background-color: {$itemBgColor};" : ""; ?>;
	color: <?php echo $itemTextColor; ?>;
	padding: <?php echo $itemPadding; ?>;
	margin: <?php echo $itemMargin; ?>;
}
#vina-carousel-content<?php echo $module->id; ?> .item a {
	color: <?php echo $itemLinkColor; ?>;
}
</style>
<div id="vina-carousel-content<?php echo $module->id; ?>" class="vina-carousel-content owl-carousel">
	<!-- Items Block -->
	<?php 
		foreach ($list as $item) :
			$title 	= $item->title;
			$link   = $item->link;
			$images = json_decode($item->images);
			$image 	= $images->image_intro;
			$image  = (empty($image)) ? $images->image_fulltext : $image;			
			$image 	= (strpos($image, 'http://') === FALSE) ? JURI::base() . $image : $image;
			$image 	= ($resizeImage) ? $thumb . '&src=' . $image : $image;
			$category 	= $item->displayCategoryTitle;
			$hits  		= $item->displayHits;
			$introtext 	= $item->displayIntrotext;
			$created   	= $item->displayDate;
	?>
	<div class="item">
		<!-- Image Block -->
		<?php if($showImage && isset($images)) : ?>
		<div class="image-block">
			<a href="<?php echo $link; ?>" title="<?php echo $title; ?>">
				<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>"/>
			</a>
		</div>
		<?php endif; ?>
		
		<!-- Text Block -->
		<?php if($showTitle || $introText || $showCategory || $showCreatedDate || $showHits || $readmore) : ?>
		<div class="text-block">
			<!-- Title Block -->
			<?php if($showTitle) :?>
			<h3 class="title">
				<a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
			</h3>
			<?php endif; ?>
			
			<!-- Info Block -->
			<?php if($showCategory || $showCreatedDate || $showHits) : ?>
			<div class="info">
				<?php if($showCreatedDate) : ?>
				<div class="blog-time">
					<span class="blog-date"><?php echo JHTML::_('date', $created, 'd');?></span>				
					<span class="blog-month"><?php echo substr(JText::sprintf(JHtml::_('date',$created, 'M')),0,3); ?></span>
					<span class="blog-year"><?php echo JText::sprintf(JHtml::_('date',$created, 'Y')); ?></span>
				</div>
				<?php endif; ?>
				
				<?php if($showCategory) : ?>
				<span><?php echo JTEXT::_('VINA_CATEGORY'); ?>: <?php echo $category; ?></span>
				<?php endif; ?>
				
				<?php if($showHits) : ?>
				<span><?php echo JTEXT::_('VINA_HITS'); ?>: <?php echo $hits; ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			
			<!-- Intro text Block -->
			<?php if($introText) : ?>
			<div class="introtext">
				<?php echo $introtext; ?>
				<!-- Readmore Block -->
				<?php if($readmore) : ?>
				<span class="readmore">
					<a class="buttonlight morebutton" href="<?php echo $link; ?>" title="<?php echo $title; ?>">
						<?php echo rtrim(JText::_('VINA_READ_MORE'), '.'); ?>
					</a>
				</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>					
		</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#vina-carousel-content<?php echo $module->id; ?>").owlCarousel({
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

        autoPlay : 		<?php echo ($autoPlay) ? 'true' : 'false'; ?>,
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