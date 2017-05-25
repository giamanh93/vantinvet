<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<div id="iframedoc"></div>
	<div id="acymailing_edit">
		<form action="<?php echo JRoute::_('index.php?option=com_acymailing&ctrl=notification'); ?>" method="post" name="adminForm"  id="adminForm" autocomplete="off" enctype="multipart/form-data">

			<table width="100%">
				<tr>
					<td valign="top">
						<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'info.form.php'); ?>
						<fieldset class="adminform" style="width:90%" id="htmlfieldset">
							<legend><?php echo JText::_( 'HTML_VERSION' ); ?></legend>
							<?php echo $this->editor->display(); ?>
						</fieldset>
						<fieldset class="adminform" style="width:90%" id="textfieldset">
							<legend><?php echo JText::_( 'TEXT_VERSION' ); ?></legend>
							<textarea style="width:98%" rows="20" name="data[mail][altbody]" id="altbody" placeholder="<?php echo JText::_('AUTO_GENERATED_HTML'); ?>"><?php echo @$this->mail->altbody; ?></textarea>
						</fieldset>
					</td>
					<td width="400" valign="top">
						<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'param.form.php'); ?>
					</td>
				</tr>
			</table>

			<div class="clr"></div>
			<input type="hidden" name="cid[]" value="<?php echo @$this->mail->mailid; ?>" />
			<input type="hidden" id="tempid" name="data[mail][tempid]" value="<?php echo @$this->mail->tempid; ?>" />
			<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
			<input type="hidden" name="data[mail][type]" value="joomlanotification" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="ctrl" value="notification" />
			<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
			echo JHTML::_( 'form.token' ); ?>
		</form>
	</div>
</div>
