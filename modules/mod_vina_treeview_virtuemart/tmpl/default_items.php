<?php
/*
# ------------------------------------------------------------------------
# Module: Vina Treeview for VirtueMart
# ------------------------------------------------------------------------
# Copyright (C) 2014 www.VinaGecko.com. All Rights Reserved.
# @license http://www.gnu.org/licenseses/gpl-3.0.html GNU/GPL
# Author: VinaGecko.com
# Websites: http://VinaGecko.com
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php
foreach($categories as $item) :
	$cid   = $item->virtuemart_category_id;
	$cname = $item->category_name;
	$child = $categoryModel->getChildCategoryList($vendorId, $cid, $fieldSort, $ordering);
	$link  = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cid);
?>
<li>
	<?php if(count($child)) : ?>
	<a href="<?php echo $link; ?>" title="<?php echo $cname; ?>">
		<span class="catTitle <?php echo (($params->get('moduleStyle') == 'filetree') ? ' folder' : ''); ?>">
			<?php echo $cname; ?>
			<?php if($count) : ?>(<?php echo modVinaTreeViewVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
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
		<span class="catTitle <?php echo (($params->get('moduleStyle') == 'filetree') ? ' file' : ''); ?>">
			<?php echo $cname; ?>
			<?php if($count) : ?>(<?php echo modVinaTreeViewVMartHelper::countProductsinCategory($cid); ?>)<?php endif; ?>
		</span>
	</a>
	<?php endif; ?>
</li>
<?php endforeach; ?>