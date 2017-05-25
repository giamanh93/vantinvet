<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage videos
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Extended videos filtering include
$this->validVideo = true;
if(is_array($this->videoFilterInclude) && count($this->videoFilterInclude)):
$found = false;
foreach ($this->videoFilterInclude as $filterInclude) :
if(stristr($this->videoTitle, trim($filterInclude))) {
	$found = true;
	break;
}
endforeach;
if(!$found):
$this->validVideo = false;
endif;
endif;

// Extended videos filtering exclude
if(is_array($this->videoFilterExclude) && count($this->videoFilterExclude)):
foreach ($this->videoFilterExclude as $filterExclude) :
if(stristr($this->videoTitle, trim($filterExclude))) {
	$this->validVideo = false;
	break;
}
endforeach;
endif;