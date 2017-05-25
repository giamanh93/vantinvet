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

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'modules/' . $module->module . '/assets/js/horizontal-menu.js');
$document->addStyleSheet(JURI::base() . 'modules/' . $module->module . '/assets/css/horizontal-menu.css');
?>
<style type="text/css" scoped >
#vina-cmenu-vmart<?php echo $module->id; ?>,
#vina-cmenu-vmart<?php echo $module->id; ?> > ul  {
	background-color: <?php echo $bgColor; ?>;
	width: <?php echo $mainWidth; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li > a {
	font-size: <?php echo $mainFontSize; ?>;
	color: <?php echo $mainTextColor; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li.has-sub::after {
	border-top-color: <?php echo $mainTextColor; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> #menu-button {
	display: none;
}
#vina-cmenu-vmart<?php echo $module->id; ?> #menu-button::after {
	border-top: 2px solid <?php echo $mainTextColor; ?>;
    border-bottom: 2px solid <?php echo $mainTextColor; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> #menu-button::before {
	border-top: 2px solid <?php echo $mainTextColor; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li.active > a,
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li:hover > a {
	color: <?php echo $mainTextHover; ?>;
	background-color: <?php echo $mainBackground; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li.active::after,
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li:hover::after {
	border-top-color: <?php echo $mainTextHover; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul ul li:hover > ul {
	left: <?php echo $subWidth + 40; ?>px;
}
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li a {
	width: <?php echo $subWidth; ?>px;
	font-size: <?php echo $subFontSize; ?>;
	color: <?php echo $subTextColor; ?>;
	border-bottom: 1px solid <?php echo $subBorder; ?>;
	background-color: <?php echo $subBackground; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li.has-sub::after {
	border-left-color: <?php echo $subTextColor; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?>.align-right ul ul li.has-sub::after {
	border-right-color: <?php echo $subTextColor; ?>;
	border-right-width: 5px;
	border-left-color: transparent;
}
#vina-cmenu-vmart<?php echo $module->id; ?>.align-right ul ul li:hover > ul {
	left: auto;
	right: 190px;
}
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li.active > a,
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li:hover > a {
	color: <?php echo $subTextHover; ?>;
	background-color: <?php echo $subBackgroundHover; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> > ul > li > ul::after {
	border-bottom-color: <?php echo $subBackground; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li.has-sub.active::after,
#vina-cmenu-vmart<?php echo $module->id; ?> ul ul li.has-sub:hover::after {
	border-left-color: <?php echo $subBackground; ?>;
}
#vina-cmenu-vmart<?php echo $module->id; ?>.align-right ul ul li.has-sub.active::after,
#vina-cmenu-vmart<?php echo $module->id; ?>.align-right ul ul li.has-sub:hover::after {
	border-right-color: <?php echo $subBackground; ?>;
}
@media all and (max-width: 979px), 
only screen and (-webkit-min-device-pixel-ratio: 2) and (max-width: 1024px), 
only screen and (min--moz-device-pixel-ratio: 2) and (max-width: 1024px), 
only screen and (-o-min-device-pixel-ratio: 2/1) and (max-width: 1024px), 
only screen and (min-device-pixel-ratio: 2) and (max-width: 1024px), 
only screen and (min-resolution: 192dpi) and (max-width: 1024px), 
only screen and (min-resolution: 2dppx) and (max-width: 1024px) {
	#vina-cmenu-vmart<?php echo $module->id; ?> li.has-sub .open-submenu:before {
		border-top-color: <?php echo $mainTextColor; ?>;
	}
	#vina-cmenu-vmart<?php echo $module->id; ?> li.has-sub .open-submenu.active::before {
		border-top-color: <?php echo $mainTextHover; ?>;
	}
}
</style>
<div id="vina-cmenu-vmart<?php echo $module->id; ?>" class="vina-cmenu-vmart <?php echo $mainAlign; ?>">
	<ul class="level0">
		<?php if($showHomeMenu) : ?>
		<li class="home<?php echo modVinaCMenuVMartHelper::isHomePage();?>">
			<a href="<?php echo JURI::base(); ?>" title="<?php echo JTEXT::_('HOME'); ?>">
				<span><?php echo JTEXT::_('HOME'); ?></span>
			</a>
		</li>
		<?php endif; ?>
		<?php require JModuleHelper::getLayoutPath($module->module, 'default_items'); ?>
	</ul>
</div>