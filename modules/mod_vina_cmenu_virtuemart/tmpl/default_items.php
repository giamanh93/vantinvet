<?php
/*
# ------------------------------------------------------------------------
# Vina Category Menu for VirtueMart for Joomla 3
# ------------------------------------------------------------------------
# Copyright(C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://vinagecko.com
# Forum:    http://vinagecko.com/forum/
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die;
?>
<?php
foreach($categories as $item) :
	$cid   = $item->virtuemart_category_id;
	$cname = $item->category_name;
	$link  = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cid;
	$link  = modVinaCMenuVMartHelper::replaceMenuItemId($link, $menuItemId);
	$link  = JRoute::_($link);
	$child = $categoryModel->getChildCategoryList($vendorId, $cid, $fieldSort, $ordering);
	$active = modVinaCMenuVMartHelper::getActiveState($cid);
?>
<li class="menu-item<?php echo (count($child)) ? ' has-sub' : ''; echo $active; ?>">
	<?php if(count($child)) : ?>
	<a href="<?php echo $link; ?>" title="<?php echo $cname; ?>">
		<span class="catTitle">
			<?php echo $cname; ?>
			<?php if($count) : ?>(<?php echo modVinaCMenuVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
		</span>
	</a>
	<ul class="sub-menu">
		<?php
			$temp 		= $categories;
			$categories = $child;
			require JModuleHelper::getLayoutPath($module->module, 'default_items');
			$categories = $temp;
		?>
	</ul>
	<?php else: ?>
	<a href="<?php echo $link; ?>" title="<?php echo $cname; ?>">
		<span class="catTitle">
			<?php echo $cname; ?>
			<?php if($count) : ?>(<?php echo modVinaCMenuVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
		</span>
	</a>
	<?php endif; ?>
</li>
<?php endforeach; ?>