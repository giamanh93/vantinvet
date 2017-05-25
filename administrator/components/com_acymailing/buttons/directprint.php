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

class JButtonDirectprint extends JButton
{
	var $_name = 'Directprint';


	function fetchButton( $type='Directprint', $namekey = '', $id = 'directprint' )
	{

		$doc = JFactory::getDocument();
		$doc->addStyleSheet( ACYMAILING_CSS.'acyprint.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyprint.css'),'text/css','print' );

		$function = "if(document.getElementById('iframepreview')){document.getElementById('iframepreview').contentWindow.focus();document.getElementById('iframepreview').contentWindow.print();}else{window.print();}return false;";

		if(!ACYMAILING_J30) {
			return '<a href="#" onclick="'.$function.'" class="toolbar"><span class="icon-32-acyprint" title="'.JText::_('ACY_PRINT',true).'"></span>'.JText::_('ACY_PRINT').'</a>';
		}

		return '<button class="btn btn-small" onclick="'.$function.'"><i class="icon-14-acyprint"></i> '.JText::_('ACY_PRINT',true).'</button>';

	}


	function fetchId( $type='Directprint', $html = '', $id = 'directprint' )
	{
		return $this->_name.'-'.$id;
	}
}

class JToolbarButtonDirectprint extends JButtonDirectprint {}
