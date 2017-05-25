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
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'modules/' . $module->module . '/assets/js/jquery.cookie.js');
$document->addScript(JURI::base() . 'modules/' . $module->module . '/assets/js/jquery.treeview.js');
$document->addStyleSheet(JURI::base() . 'modules/' . $module->module . '/assets/css/jquery.treeview.css');
?>
<div id="vina-treeview-virtuemart<?php echo $module->id; ?>" class="vina-treeview-virtuemart">
	<?php if($params->get('showControl', 1)) { ?>
	<div id="vina-treeview-treecontrol<?php echo $module->id; ?>" class="treecontrol">
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_COLLAPSE_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_COLLAPSE_ALL'); ?></a> | 
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_EXPAND_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_EXPAND_ALL'); ?></a> | 
        <a href="#" title="<?php echo JTEXT::_('VINA_TREEVIEW_VMART_TOGGLE_ALL_DESC'); ?>"><?php echo JTEXT::_('VINA_TREEVIEW_VMART_TOGGLE_ALL'); ?></a>
    </div>
	<?php } ?>
	
	<ul class="level0 <?php echo $params->get('moduleStyle', ''); ?>">
		<?php require JModuleHelper::getLayoutPath($module->module, 'default_items'); ?>
	</ul>
</div>
<script type="text/javascript">
jQuery("#vina-treeview-virtuemart<?php echo $module->id; ?> ul").treeview({
	animated: 	"<?php echo $params->get('animated', 1); ?>",
	persist: 	"<?php echo $params->get('persist', 'cookie'); ?>",
	collapsed: 	<?php echo $params->get('collapsed', 1) ? "true" : "false"; ?>,
	unique:		<?php echo $params->get('unique', 1) ? "true" : "false"; ?>,
	<?php if($params->get('showControl', 1)) { ?>
	control: "#vina-treeview-treecontrol<?php echo $module->id; ?>",
	<?php } ?>
});
</script>