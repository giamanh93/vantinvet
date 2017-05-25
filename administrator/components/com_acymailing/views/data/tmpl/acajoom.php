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
	$db = JFactory::getDBO();
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('acajoom_subscribers',false));
	$resultUsers = $db->loadResult();
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('acajoom_lists',false));
	$resultLists = $db->loadResult();

	echo JText::sprintf('USERS_IN_COMP',$resultUsers,'Acajoom');

	if(!empty($resultLists)){
		echo '<fieldset class="adminform"><legend>'.JText::sprintf('LISTS_IN_COMP',$resultLists,'Acajoom').'</legend>';
		echo JText::sprintf('IMPORT_X_LISTS',$resultLists).'<br />';
	 	echo JText::sprintf('IMPORT_LIST_TOO','Acajoom').JHTML::_('acyselect.booleanlist', "acajoom_lists");
		echo '</fieldset>';
	}
