<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

//Addding Main CSS/JS VM_Theme files to header
JHtml::stylesheet(VM_THEMEURL.'theme.css', array(), false);
JHtml::stylesheet('components/com_wishlist/template.css', array(), false);

$itemid = JRequest::getInt('Itemid',  1);
$i = 0;
$my_page =& JFactory::getDocument();
$conf =& JFactory::getConfig();
$sitename = $conf->get('config.sitename');
$my_page->setTitle($sitename. ' - ' .JText::_( 'VM_SHARED_LIST' )); 
?>
<?php 	if (empty( $this->data )){ ?> <h2 class='fav_header'><?php echo JText::_('VM_SHAREDLISTS_EMPTY') ?></h2> <?php	}
else 
{ 
?>
	<h2 class="fav_title"><?php echo JText::_( 'VM_SHARED_LIST' ); ?></h2>
	<div class='fav_table'>
		<div class='fav_heading'>
			<div class='fav_col'>
				<?php echo JText::_( 'FW_TYPE' ); ?>
			</div>
			<div class='fav_col'>
				<?php echo JText::_( 'SHARE_DATE' ); ?>
			</div>
			<div class='fav_col'>
				<?php echo JText::_( 'USER_NAME' ); ?>
			</div>
			<div class='fav_col'>
				<?php echo JText::_( 'SHARE_TITLE' ); ?>
			</div>
		</div>
<?php	
	foreach($this->data as $dataItem)
	{
		$link = JRoute::_( "index.php?option=com_wishlist&view=sharelist&user_id={$dataItem->user_id}&Itemid={$itemid}" );
		?>
		<div class='fav_row'>
			<div class='fav_col'>
			<?php if ($dataItem->isWishList) { ?>
				<img src="components/com_wishlist/images/wishlist.png" title="<?php echo JText::_( 'VM_WISHLIST_TRUE' ); ?>" alt="<?php echo JText::_( 'VM_WISHLIST_TRUE' ); ?>" />
				<?php } 
			else {
				?>
				<img src="components/com_wishlist/images/favorites.png" title="<?php echo JText::_( 'VM_FAVORITES_TRUE' ); ?>" alt="<?php echo JText::_( 'VM_FAVORITES_TRUE' ); ?>" />
				<?php } ?>
			</div>
			<div class='fav_col'>
			<h4><?php echo JHtml::date($dataItem->share_date, JText::_('DATE_FORMAT_LC4')); ?></h4>
			</div>
			<div class='fav_col'>
			<h4><?php echo $dataItem->name; ?></h4>
			</div>
			<div class='fav_col'>
			<h4><?php echo $dataItem->share_title; ?></h4>
			</div>
			<div class='fav_col'>
			<!-- You can use $link var for link edit controller -->
			<h4><a href="<?php echo $link; ?>">View</a></h4>
			</div>
		</div>	
		<?php
	} ?>
	</div>
<?php
}
?>
<div class="pagination">
	<?php echo str_replace('</ul>', '<li class="counter">'.$this->pagination->getPagesCounter().'</li></ul>', $this->pagination->getPagesLinks()); ?>
</div>

