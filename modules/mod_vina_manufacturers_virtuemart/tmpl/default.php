<?php
/*
# ------------------------------------------------------------------------
# Vina Manufacturers Carousel for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/
defined('_JEXEC') or die('Restricted access');
$doc = JFactory::getDocument();
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.carouFredSel.min.js', 'text/javascript');
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.mousewheel.min.js', 'text/javascript');
#$doc->addScript('modules/' . $module->module . '/assets/js/jquery.touchSwipe.min.js', 'text/javascript');
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.transit.min.js', 'text/javascript');
$doc->addScript('modules/' . $module->module . '/assets/js/jquery.ba-throttle-debounce.min.js', 'text/javascript');
$doc->addStyleSheet('modules/' . $module->module . '/assets/css/carouFredSel.css');
?>
<style type="text/css">
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> {
	max-width: <?php echo $moduleWidth; ?>;
	height: <?php echo $moduleHeight; ?>;
	margin: <?php echo $moduleMargin; ?>;
	padding: <?php echo $modulePadding; ?>;
	<?php echo ($bgImage != '') ? "background: url({$bgImage}) repeat scroll 0 0;" : ''; ?>
	<?php echo ($isBgColor) ? "background-color: {$bgColor};" : '';?>
}
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> li {
	margin: <?php echo $itemMargin; ?>;
}
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> .vina-caption,
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> .vina-caption a {
	background: <?php echo $captionBgColor; ?>;
	color:<?php echo $captionColor; ?>;
}
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> .vina-pager > a > span {
	color: <?php echo $textPagination; ?>;
	background-color: <?php echo $bgPagination; ?>;
}
#vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?> .vina-pager a.selected > span{
	color: <?php echo $textPaginationA; ?>;
	background-color: <?php echo $bgPaginationA; ?>;
}
</style>
<div id="vina-manufacturers-virtuemart-wrapper<?php echo $module->id; ?>" class="vina-manufacturers-virtuemart">
	<ul id="vina-manufacturers-virtuemart<?php echo $module->id; ?>">
		<?php
			foreach($manufacturers as $manufacturer) : 
				$mid   = $manufacturer->virtuemart_manufacturer_id;
				$mlink = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id=' . $mid);
				$mname = $manufacturer->mf_name;
				$mlogo = $manufacturer->images[0]->file_url;
				$mlogo = (!empty($mlogo)) ? JURI::base() . $mlogo : $mlogo;
		?>
		<li class="item">
			<!-- Image Block -->
			<?php if($showImage): ?>
				<?php if($linkOnImage): ?>
				<a href="<?php echo $mlink; ?>" title="<?php echo $mname; ?>">
					<img src="<?php echo $mlogo; ?>" alt="<?php echo $mname; ?>" />
				</a>
				<?php else: ?>
				<img src="<?php echo $mlogo; ?>" alt="<?php echo $mname; ?>" />
				<?php endif; ?>
			<?php endif; ?>
			
			<!-- Caption Block -->
			<?php if($showName): ?>
			<div class="vina-caption">
				<?php if($linkOnName): ?>
				<a href="<?php echo $mlink; ?>" title="<?php echo $mname; ?>"><?php echo $mname; ?></a>
				<?php else: ?>
				<?php echo $mname; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
	
	<!-- Arrow Navigation Block -->
	<?php if($navigation): ?>
	<div id="vina-prev<?php echo $module->id; ?>" class="vina-prev">Prev</div>
	<div id="vina-next<?php echo $module->id; ?>" class="vina-next">Next</div>
	<?php endif; ?>
	
	<!-- Pagination Block -->
	<?php if($pagination): ?>
	<div id="vina-pager<?php echo $module->id; ?>" class="vina-pager"></div>
	<?php endif; ?>
</div>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	$(window).load(function(){
		$('#vina-manufacturers-virtuemart<?php echo $module->id; ?>').carouFredSel({
			width: 		"100%",
			items: 		<?php echo $noItems; ?>,
			circular: 	<?php echo $circular ? 'true' : 'false'; ?>,
			infinite: 	<?php echo $infinite ? 'true' : 'false'; ?>,
			auto: 		<?php echo $auto ? 'true' : 'false'; ?>,
			mousewheel: <?php echo $mousewheel ? 'true' : 'false'; ?>,
			direction: 	"<?php echo $direction; ?>",
			align: 		"<?php echo $align; ?>",
			prev: 		'#vina-prev<?php echo $module->id; ?>',
			next: 		'#vina-next<?php echo $module->id; ?>',
			pagination: "#vina-pager<?php echo $module->id; ?>",
			swipe: {
				onMouse: <?php echo $mouseSwipe ? 'true' : 'false'; ?>,
				onTouch: <?php echo $touchSwipe ? 'true' : 'false'; ?>
			},
			scroll: {
				items           : <?php echo $scrollItems; ?>,
				fx				: "<?php echo $fx; ?>",
				easing          : "<?php echo $easing; ?>",
				duration        : <?php echo $duration; ?>,                         
				pauseOnHover    : <?php echo $pauseOnHover ? 'true' : 'false'; ?>
			}  
		});
	});
});
</script>