<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-security">
<?php if(acymailing_level(1)) {
} ?>

	<fieldset class="adminform">
	<legend><?php echo JText::_('ADVANCED_EMAIL_VERIFICATION'); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('CHECK_DOMAIN_EXISTS'); ?>
				</td>
				<td>
					<?php
					if(function_exists('getmxrr')){
						echo JHTML::_('acyselect.booleanlist', "config[email_checkdomain]" , '',$this->config->get('email_checkdomain',0) );
					}else{
						echo 'Function getmxrr not enabled';
					}
					 ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::sprintf('X_INTEGRATION','BotScout'); ?>
				</td>
				<td>
					<?php echo JHTML::_('acyselect.booleanlist', "config[email_botscout]" , '',$this->config->get('email_botscout',0) ); ?>
					<br />API Key: <input class="inputbox" type="text" name="config[email_botscout_key]" style="width:100px" value="<?php echo $this->escape($this->config->get('email_botscout_key')) ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::sprintf('X_INTEGRATION','StopForumSpam'); ?>
				</td>
				<td>
					<?php echo JHTML::_('acyselect.booleanlist', "config[email_stopforumspam]" , '',$this->config->get('email_stopforumspam',0) ); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo acymailing_tooltip(JText::_('IPTIMECHECK_DESC'), JText::_('IPTIMECHECK'), '', JText::_('IPTIMECHECK')); ?>
				</td>
				<td>
					<?php echo JHTML::_('acyselect.booleanlist', "config[email_iptimecheck]" , '',$this->config->get('email_iptimecheck',0) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset class="adminform">
	<legend><?php echo JText::_( 'ACY_FILES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo acymailing_tooltip(JText::_('ALLOWED_FILES_DESC'), JText::_('ALLOWED_FILES'), '', JText::_('ALLOWED_FILES')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[allowedfiles]" style="width:250px" value="<?php echo $this->escape(strtolower(str_replace(' ','',$this->config->get('allowedfiles')))); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing_tooltip(JText::_('UPLOAD_FOLDER_DESC'), JText::_('UPLOAD_FOLDER'), '', JText::_('UPLOAD_FOLDER')); ?>
				</td>
				<td>
					<?php $uploadfolder = $this->config->get('uploadfolder'); if(empty($uploadfolder)) $uploadfolder = 'media/com_acymailing/upload'; ?>
					<input class="inputbox" type="text" name="config[uploadfolder]" style="width:250px" value="<?php echo $this->escape($uploadfolder); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing_tooltip(JText::_('MEDIA_FOLDER_DESC'), JText::_('MEDIA_FOLDER'), '', JText::_('MEDIA_FOLDER')); ?>
				</td>
				<td>
					<?php $mediafolder = $this->config->get('mediafolder','media/com_acymailing/upload'); if(empty($mediafolder)) $mediafolder = 'media/com_acymailing/upload'; ?>
					<input class="inputbox" type="text" name="config[mediafolder]" style="width:250px" value="<?php echo $this->escape($mediafolder); ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'DATABASE_MAINTENANCE' ); ?></legend>
		<table class="admintable" cellspacing="1">
<?php if(acymailing_level(1)) { ?>
			<tr>
				<td class="key" >
					<?php echo acymailing_tooltip(JText::_('DATABASE_MAINTENANCE_DESC').'<br />'.JText::_('DATABASE_MAINTENANCE_DESC2'), JText::_('DELETE_DETAILED_STATS'), '', JText::_('DELETE_DETAILED_STATS')); ?>
				</td>
				<td>
					<?php echo $this->elements->delete_stats; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('DATABASE_MAINTENANCE_DESC').'<br />'.JText::_('DATABASE_MAINTENANCE_DESC2'), JText::_('DELETE_HISTORY'), '', JText::_('DELETE_HISTORY')); ?>
				</td>
				<td>
					<?php echo $this->elements->delete_history; ?>
				</td>
			</tr>
<?php } ?>
			<tr>
				<td class="key">
				<?php echo JText::_('DATABASE_INTEGRITY'); ?>
				</td>
				<td>
					<?php echo $this->elements->checkDB; ?>
				</td>
			</tr>
		</table>
	</fieldset>

</div>
