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
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('nspro_subs',false));
	$resultUsers = $db->loadResult();
	$db->setQuery('SELECT count(id) FROM '.acymailing_table('nspro_lists',false));
	$resultLists = $db->loadResult();
?>

<table class="admintable" cellspacing="1">
	<tr>
		<td colspan="2">
			<?php echo JText::sprintf('USERS_IN_COMP',$resultUsers,'NS Pro'); ?>
			<br />
			<?php echo JText::sprintf('LISTS_IN_COMP',$resultLists,'NS Pro'); ?>
			<br />
			<?php echo JText::sprintf('IMPORT_X_LISTS',$resultLists);?>
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::sprintf('IMPORT_LIST_TOO','NS Pro'); ?>
		</td>
		<td>
			<?php echo JHTML::_('acyselect.booleanlist', "nspro_lists"); ?>
		</td>
	</tr>
</table>
