<?php
 /**
 * ------------------------------------------------------------------------
 * JU Tooltip plugin for Joomla 2.5
 * ------------------------------------------------------------------------
 * Copyright (C) 2010-2012 JoomUltra. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: JoomUltra Co., Ltd
 * Websites: http://www.joomultra.com
 * ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * JU Tooltip plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.jutooltip
 */
class plgSystemJUTooltip extends JPlugin
{
	var $_body = NULL;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	 public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
		
    private function addheadtag($tagtype, $data)
    {
        $document = JFactory::getDocument();
        $styles = $document->_styleSheets;
        $scripts = $document->_scripts;
		$style_arr = array();
		$script_arr = array();
		$data = array_unique($data);
		
		if ($tagtype=="js") {
			foreach ($scripts AS $key => $value) {
				$script_arr[] = $key;
			}
			foreach ($data AS $item) {
				if (!in_array($item, $script_arr)) {
					$headtag_item = '<script src="' . $item . '" type="text/javascript"></script>';
					if (strpos($this->_body, $headtag_item)===false) $headtag[] = $headtag_item;
				}
			}
		}
		
		if ($tagtype=="css") {
			foreach ($styles AS $key => $value) {
				$style_arr[] = $key;
			}
			foreach ($data AS $item) {
				if (!in_array($item, $style_arr)) {
					$headtag_item = '<link href="' . $item . '" type="text/css" rel="stylesheet"/>';
					if (strpos($this->_body, $headtag_item)===false) $headtag[] = $headtag_item;
				}
			}
		}
		
		if ($tagtype=="javascript") $headtag[] = '<script type="text/javascript">' . implode("\n", $data) . '</script>';
		
		if(!empty($headtag)) {
			$this->_body = str_replace('</head>', "\t" . implode("\n\t", $headtag) . "\n</head>", $this->_body);
		}
    }
	
	/*
	* Check if jQurey is loaded or not
	*/
	protected function checkjQuery()
	{
		$body = JResponse::getBody();
		$regex= '#\<script.* src="([\/\\a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|core|min|pack)*?.js".*\>\<\/script\>#m';
		preg_match($regex, $body, $matches);
		if (empty($matches)) return false;
		else return true;
	}
	
	/*
	* Check if jQurey easing is loaded or not
	*/
	protected function checkjQueryEasing()
	{
		$body = JResponse::getBody();
		$regex= '#\<script.* src="([\/\\a-zA-Z0-9_:\.-]*)jquery.easing([0-9\.-]|core|min|pack)*?.js".*\>\<\/script\>#m';
		preg_match($regex, $body, $matches);
		if (empty($matches)) return false;
		else return true;
	}
	
	/**
	* @since	1.6
	*/
	public function onAfterRender()
	{
        $app = JFactory::getApplication();
        if ($app->isAdmin())
            return;
		
		$this->_body = JResponse::getBody();
		
		$ju_tooltip_param[] = "effect: '".$this->params->get('effect','toggle')."'";
		$ju_tooltip_param[] = "showDuration: ".$this->params->get('showDuration','200');
		$ju_tooltip_param[] = "hideDuration: ".$this->params->get('hideDuration','200');
		$ju_tooltip_param[] = "easing: '".$this->params->get('easing','linear')."'";
		$ju_tooltip_param[] = "predelay: ".$this->params->get('predelay','0');
		$ju_tooltip_param[] = "delay: ".$this->params->get('delay','30');
		$ju_tooltip_param[] = "opacity: ".$this->params->get('opacity','1');
		$ju_tooltip_param[] = "tip: '".$this->params->get('tip','')."'";
		$ju_tooltip_param[] = "fadeIE: ".$this->params->get('fadeIE','false');
		$ju_tooltip_param[] = "position : ['".$this->params->get('vertical','top')."','".$this->params->get('horizontal','center')."']";
		$ju_tooltip_param[] = "offset: ".$this->params->get('offset','[0, 0]');
		$ju_tooltip_param[] = "cancelDefault: ".$this->params->get('cancelDefault','true');
		$ju_tooltip_param[] = "manualCloseTooltip: ".$this->params->get('manualCloseTooltip','false');
		if ($this->params->get('showtooltipwhen','mouseenter')=='click') {
			$ju_tooltip_param[] = "events: {
				def: \"click,mouseleave\"
				}";
		}
		$ju_tooltip_param[] = "direction: '".$this->params->get('slide_direction','up')."'";
		$ju_tooltip_param[] = "bounce: ".$this->params->get('bounce','false');
		$ju_tooltip_param[] = "slideOffset: ".$this->params->get('slideOffset','10');

		$ju_tooltip_param_str = implode(", ",$ju_tooltip_param);
		
		if ($this->params->get('slide_direction','up')=='up') {
			$dynamic_param = "top: { direction: 'up' } ";
		} elseif ($this->params->get('slide_direction','up')=='down') {
			$dynamic_param = "bottom: { direction: 'down' } ";
		}
		$tooltip_javascript[] = "jQuery(document).ready(function($){
			jutooltip_api = $('.jutooltip').jutooltip({".$ju_tooltip_param_str."}).jutooltip_dynamic({".$dynamic_param."});
			});";
		
		$tooltip_css[] = JURI::Base()."plugins/system/jutooltip/assets/css/styles.css";
		$tooltip_css[] = JURI::Base()."plugins/system/jutooltip/assets/themes/".$this->params->get('theme','default')."/styles.css";
		
		//Load jQuery
		if( $this->params->get('loadjquery', '2')==1 || ($this->params->get('loadjquery', '2')==2 && !plgSystemJUTooltip::checkjQuery()) ) {
			if($this->params->get('loadjqueryfrom', '1')==1) { $tooltip_js[] = JURI::Base()."plugins/system/jutooltip/assets/js/jquery.min.js"; }
			else if($this->params->get('loadjqueryfrom', '1')==2) { $tooltip_js[] = "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"; }
		}
		
		//Only load jQuery easing if it has not been loaded
		if ( !plgSystemJUTooltip::checkjQueryEasing() )	$tooltip_js[] = JURI::Base()."plugins/system/jutooltip/assets/js/jquery.easing.1.3.min.js";
		$tooltip_js[] = JURI::Base()."plugins/system/jutooltip/assets/js/jutooltip.min.js";
		$tooltip_js[] = JURI::Base()."plugins/system/jutooltip/assets/js/jutooltip.effects.min.js";
		$tooltip_js[] = JURI::Base()."plugins/system/jutooltip/assets/js/jutooltip.dynamic.min.js";
		
		plgSystemJUTooltip::addheadtag('css', $tooltip_css);
		plgSystemJUTooltip::addheadtag('js', $tooltip_js);
		plgSystemJUTooltip::addheadtag('javascript', $tooltip_javascript);
		
		JResponse::setBody($this->_body);
	}
}
