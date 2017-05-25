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
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('jnews_subscribers',false));
	$resultUsers = $db->loadResult();
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('jnews_lists',false));
	$resultLists = $db->loadResult();
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('jnews_mailings',false));
	$resultNews = $db->loadResult();

	echo JText::sprintf('USERS_IN_COMP',$resultUsers,'jNews');
if(!empty($resultLists)){
	echo '<fieldset class="adminform"><legend>'.JText::sprintf('LISTS_IN_COMP',$resultLists,'jNews').'</legend>';
	echo JText::sprintf('IMPORT_X_LISTS',$resultLists).'<br />';
 	echo JText::sprintf('IMPORT_LIST_TOO','jNews').JHTML::_('acyselect.booleanlist', "jnews_lists");
	echo '</fieldset>';
}
if(!empty($resultNews)){
	echo '<fieldset class="adminform"><legend>'.JText::sprintf('NEWS_IN_COMP',$resultNews,'jNews').'</legend>';
	echo JText::sprintf('IMPORT_NEWSLETTERS_TOO','jNews').JHTML::_('acyselect.booleanlist', "jnews_news");
}
