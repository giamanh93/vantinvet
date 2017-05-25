<?php
/**
 * @package Helix Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2014 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

//[Gallery]
if(!function_exists('gallery_sc')) {
	$galleryArray = array();
	function gallery_sc( $atts, $content="" ){
		global $galleryArray;		
		$tags = array();
		
		extract($params = shortcode_atts(array(
			'columns' => 3,
			'modal' => 'yes',
			'filter' => 'no',
			'caption' => 'no',
			'id' => 'modal-default'
		), $atts));
		
		do_shortcode( $content );
		
		//Add gallery.css file
		Helix::addShortcodeStyle('gallery.css');
		//isotope
		
		if($filter=='yes') { Helix::addShortcodeScript('jquery.isotope.min.js'); }
		
		$tags = '';
		
		foreach ($galleryArray as $key=>$item) $tags .= ',' . $item['tag'];
		
		$tags = ltrim($tags, ',');
		$tags = explode(',', $tags);
		$newtags = array();
		foreach($tags as $tag) $newtags[] = trim($tag);
		$tags = array_unique($newtags);
		//var_dump($item);die;
		ob_start(); 
		if($filter=='yes') {
		?>
		
		<div class="gallery-filters btn-group">
			<a class="btn active" href="#" data-filter="*"><?php echo JText::_('All'); ?></a>
			<?php foreach ($tags as $tag) { ?>		  
				<a class="btn" href="#" data-filter=".<?php echo str_replace(" ", "-", $tag); ?>"><?php echo ucfirst(trim($tag)) ?></a>
			<?php } ?>
		</div>
		<?php }?>	
		<ul class="gallery">
		<?php foreach ($galleryArray as $key=>$item) { ?>	
				<li style="width:<?php echo round(100/$columns); ?>%" class="gallery-item <?php $itag_li = str_replace(" ", "-", $item['tag']); echo str_replace(array(',',' -'), " ", $itag_li);?>">
					<a class="img-polaroid" data-toggle="modal" href="<?php echo ($modal=='yes')? '#'.$params['id'].'-modal-' . $key . '':'#' ?>">
						<?php
							echo '<img alt="'.$item['src'].'" src="' . $item['src'] . '" />';
						?>
						<?php if($caption == 'yes' || $item['content'] !='') { ?>
							<div class="gallery-caption">													
								<?php if($caption == 'yes') { ?>						
									<div class="caption-body">
										<a class="a-icon-search" data-toggle="modal" href="<?php echo ($modal=='yes')? '#'.$params['id'].'-modal-' . $key . '':'#' ?>"><i class="icon-search"></i></a>
										<a class="a-icon-link" href=""><i class="icon-link"></i></a>							
									</div>
								<?php } ?>
								<?php if($item['content'] !='') { ?>
									<div class="caption-content">
										<?php echo do_shortcode($item['content']); ?>
									</div>
								<?php } ?>
							</div>
						<?php } ?>
					</a>					
				</li>
				
				<?php if($modal=='yes') { ?>
				<li class="gallery-modal">
					<div id="<?php echo $params['id']; ?>-modal-<?php echo $key; ?>" class="modal hide fade" tabindex="-1">
						<a class="close-modal" href="javascript:;" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i></a>								
						<div class="modal-body">
							<?php echo '<img src="' . $item['src'] . '" alt=" " style="width:100%; max-height:400px" />';?>
						</div>
					</div>
				</li>
				<?php } ?>
				
			<?php } ?>
		</ul>
		
		<?php if($filter=='yes') { ?>
			<script type="text/javascript">
				spnoConflict(function($){
					$(window).load(function(){
						$gallery = $('.gallery');
						$gallery.isotope({
				  			// options
				  			itemSelector : 'li.gallery-item',
				  			layoutMode : 'fitRows'
						});

						$filter = $('.gallery-filters');
						$selectors = $filter.find('>a');

						$filter.find('>a').click(function(){
							var selector = $(this).attr('data-filter');

							$selectors.removeClass('active');
							$(this).addClass('active');
							
							$gallery.isotope({ filter: selector });
							return false;
						});
					});
				});
			</script>
		<?php } ?>
		  
		<?php
		$galleryArray = array();	
		//return $html;
		return ob_get_clean();
	}
	add_shortcode( 'gallery', 'gallery_sc' );
	
	//Accordion Items
	function gallery_item_sc( $atts, $content="" ){
		global $galleryArray;
		$galleryArray[] = array(
			'src'=>(isset($atts['src'])?$atts['src']:''),
			'tag'=>(isset($atts['tag']) && $atts['tag'] !='')?$atts['tag']:'',
			'content'=>$content
		);
	}

	add_shortcode( 'gallery_item', 'gallery_item_sc' );
}