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

jimport('joomla.filesystem.file');
class modVinaCameraImageSliderHelper
{
    public static function getSildes($slider)
	{
        switch($slider->src)
		{
			default:
					$rows = $slider->list;
				break;
		}
		return $rows;
    }
	
	public static function getCopyrightText($module)
	{
		echo '<div id="vina-copyright'.$module->id.'">© Free <a href="http://vinagecko.com/joomla-modules" title="Free Joomla! 3 Modules">Joomla! 3 Modules</a>- by <a href="http://vinagecko.com/" title="Beautiful Joomla! 3 Templates and Powerful Joomla! 3 Modules, Plugins.">VinaGecko.com</a></div>';
	}
}