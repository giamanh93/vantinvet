<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class JButtonAcyabtesting extends JButton
{
	var $_name = 'Acyabtesting';

	function fetchButton( $type='Acyabtesting')
	{
		$url = JURI::base()."index.php?option=com_acymailing&ctrl=newsletter&task=abtesting&tmpl=component";
		$top = 0; $left = 0;
		$width = 800;
		$height = 600;

		$text	= JText::_('ABTESTING');
		if((!ACYMAILING_J30))	$class	= "icon-32-acyabtesting";
		else $class	= "icon-14-acyabtesting";

		$js = "
function getAcyAbTestingUrl() {
	i = 0;
	mylink = 'index.php?option=com_acymailing&ctrl=newsletter&task=abtesting&tmpl=component&mailid=';
	mymailids = '';
	while(window.document.getElementById('cb'+i)){
		if(window.document.getElementById('cb'+i).checked)
			mymailids += window.document.getElementById('cb'+i).value+',';
		i++;
	}
	mylink += mymailids.slice(0,-1);
	return mylink;
}
";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		if(!ACYMAILING_J30) {
			JHTML::_('behavior.modal','a.modal');
			return '<a href="'.$url.'" class="modal" onclick="this.href=getAcyAbTestingUrl();" rel="{handler: \'iframe\', size: {x: '.$width.', y: '.$height.'}}"><span class="'.$class.'" title="'.$text.'"></span>'.$text.'</a>';
		}

		$html = '<button class="btn btn-small modal" data-toggle="modal" data-target="#modal-'.$type.'"><i class="'.$class.'"></i> '.$text.'</button>';
		$params['title']  = $text;
		$params['url']    = '\'+getAcyAbTestingUrl()+\''; //$url;
		$params['height'] = $height;
		$params['width']  = $width;
		$modalHtml = JHtml::_('bootstrap.renderModal', 'modal-'.$type, $params);
		$html .= str_replace(
				array('id="modal-'.$type.'"'),
				array('id="modal-'.$type.'" style="width:'.($width+20).'px;height:'.($height+90).'px;margin-left:-'.(($width+20)/2).'px"'),
				$modalHtml
		);
		$html .= '<script>'."\r\n" . 'jQuery(document).ready(function(){jQuery("#modal-'.$type.'").appendTo(jQuery(document.body));});'."\r\n".'</script>';
		return $html;
	}

	function fetchId($name)
	{
		return "toolbar-popup-Acyabtesting";
	}
}

class JToolbarButtonAcyabtesting extends JButtonAcyabtesting {}
