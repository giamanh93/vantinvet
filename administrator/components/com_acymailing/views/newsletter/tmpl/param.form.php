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
if(acymailing_isAllowed($this->config->get('acl_newsletters_lists','all')) || acymailing_isAllowed($this->config->get('acl_newsletters_attachments','all')) || acymailing_isAllowed($this->config->get('acl_newsletters_sender_informations','all')) || acymailing_isAllowed($this->config->get('acl_newsletters_meta_data','all')) || acymailing_isAllowed($this->config->get('acl_newsletters_inbox_actions','all'))){ ?>
	 <div id="newsletterparams">

	<?php echo $this->tabs->startPane( 'mail_tab');

	if(!acymailing_isAllowed($this->config->get('acl_newsletters_lists','all')) || $this->type == 'joomlanotification'){
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(" .mail_receivers_acl{display:none;} ");
		echo '<div class="mail_receivers_acl">';
	}else{
		echo $this->tabs->startPanel(JText::_( 'LISTS' ), 'mail_receivers');
	} ?>
	<br style="font-size:1px"/>
	<?php
	if(empty($this->lists)){
		echo JText::_('LIST_CREATE');
	}else{
		echo JText::_('LIST_RECEIVERS');
		$currentPage = 'newsletter';
		include_once(ACYMAILING_BACK.'views'.DS.'list'.DS.'tmpl'.DS.'filter.lists.php');

		if(acymailing_level(2) && acymailing_isAllowed($this->config->get('acl_lists_filter','all'))) include_once(dirname(__FILE__).DS.'filters.php');
	}
	if(!acymailing_isAllowed($this->config->get('acl_newsletters_lists','all')) || $this->type == 'joomlanotification') echo '</div>';
	else echo $this->tabs->endPanel();

 	if(acymailing_isAllowed($this->config->get('acl_newsletters_attachments','all'))){
	 	echo $this->tabs->startPanel(JText::_( 'ATTACHMENTS' ), 'mail_attachments');?>
		<br style="font-size:1px"/>
		<?php if(!empty($this->mail->attach)){?>
			<fieldset class="adminform" id="attachmentfieldset">
			<legend><?php echo JText::_( 'ATTACHED_FILES' ); ?></legend>
			<?php
				foreach($this->mail->attach as $idAttach => $oneAttach){
					$idDiv = 'attach_'.$idAttach;
					echo '<div id="'.$idDiv.'">'.$oneAttach->filename.' ('.(round($oneAttach->size/1000,1)).' Ko)';
					echo $this->toggleClass->delete($idDiv,$this->mail->mailid.'_'.$idAttach,'mail');
					echo '</div>';
				}
			?>
			</fieldset>
		<?php } ?>
		<div id="loadfile">
			<input type="file" style="width:auto;" name="attachments[]" />
		</div>
		<a href="javascript:void(0);" onclick='addFileLoader()'><?php echo JText::_('ADD_ATTACHMENT'); ?></a>
		<?php echo JText::sprintf('MAX_UPLOAD',$this->values->maxupload);?>
		<?php echo $this->tabs->endPanel();
	}

	if(!acymailing_isAllowed($this->config->get('acl_newsletters_sender_informations','all'))){
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(" .mail_sender_acl{display:none;} ");
		echo '<div id="mail_sender_acl" style="display:none" >';
	}else{
		echo $this->tabs->startPanel(JText::_( 'SENDER_INFORMATIONS' ), 'mail_sender');
	}?>
	<br style="font-size:1px"/>
	<table width="100%" class="paramlist admintable" id="senderinformationfieldset">
		<tr>
			<td class="paramlist_key">
				<label for="fromname"><?php echo JText::_( 'FROM_NAME' ); ?></label>
			</td>
			<td class="paramlist_value">
				<input placeholder="<?php echo JText::_( 'USE_DEFAULT_VALUE' ); ?>" class="inputbox" id="fromname" type="text" name="data[mail][fromname]" style="width:200px; max-width:80%;" value="<?php echo $this->escape(@$this->mail->fromname); ?>" />
			</td>
		</tr>
		<tr>
			<td class="paramlist_key">
				<label for="fromemail"><?php echo JText::_( 'FROM_ADDRESS' ); ?></label>
			</td>
			<td class="paramlist_value">
				<input onchange="validateEmail(this.value, '<?php echo addslashes(JText::_('FROM_ADDRESS')); ?>')" placeholder="<?php echo JText::_( 'USE_DEFAULT_VALUE' ); ?>" class="inputbox" id="fromemail" type="text" name="data[mail][fromemail]" style="width:200px; max-width:80%;" value="<?php echo $this->escape(@$this->mail->fromemail); ?>" />
			</td>
		</tr>
		<tr>
		<td class="paramlist_key">
			<label for="replyname"><?php echo JText::_( 'REPLYTO_NAME' ); ?></label>
			</td>
			<td class="paramlist_value">
				<input placeholder="<?php echo JText::_( 'USE_DEFAULT_VALUE' ); ?>" class="inputbox" id="replyname" type="text" name="data[mail][replyname]" style="width:200px; max-width:80%;" value="<?php echo $this->escape(@$this->mail->replyname); ?>" />
			</td>
		</tr>
		<tr>
		<td class="paramlist_key">
			<label for="replyemail"><?php echo JText::_( 'REPLYTO_ADDRESS' ); ?></label>
			</td>
			<td class="paramlist_value">
				<input onchange="validateEmail(this.value, '<?php echo addslashes(JText::_('REPLYTO_ADDRESS')); ?>')" placeholder="<?php echo JText::_( 'USE_DEFAULT_VALUE' ); ?>" class="inputbox" id="replyemail" type="text" name="data[mail][replyemail]" style="width:200px; max-width:80%;" value="<?php echo $this->escape(@$this->mail->replyemail); ?>" />
			</td>
		</tr>
	</table>
<?php
	echo acymailing_getFunctionsEmailCheck();

	if(!acymailing_isAllowed($this->config->get('acl_newsletters_sender_informations','all'))) echo '</div>';
	else echo $this->tabs->endPanel();

	if($this->type == 'joomlanotification'){
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration(" .mail_metadata_jnotif{display:none;} ");
		echo '<div class="mail_metadata_jnotif">';
	}else{
		if(acymailing_isAllowed($this->config->get('acl_newsletters_meta_data','all'))){
			echo $this->tabs->startPanel(JText::_( 'META_DATA' ), 'mail_metadata');?>
			<br style="font-size:1px"/>
			<table width="100%" class="paramlist admintable" id="metadatatable">
				<tr>
					<td class="paramlist_key">
						<label for="metakey"><?php echo JText::_( 'META_KEYWORDS' ); ?></label>
					</td>
					<td class="paramlist_value">
						<textarea id="metakey" name="data[mail][metakey]" rows="5" style="width:200px; max-width:80%;" ><?php echo @$this->mail->metakey; ?></textarea>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key">
						<label for="metadesc"><?php echo JText::_( 'META_DESC' ); ?></label>
					</td>
					<td class="paramlist_value">
						<textarea id="metadesc" name="data[mail][metadesc]" rows="5" style="width:200px; max-width:80%;" ><?php echo @$this->mail->metadesc; ?></textarea>
					</td>
				</tr>
			</table>
			<?php
			echo $this->tabs->endPanel();
		}
	}
	if($this->type == 'joomlanotification') echo '</div>';
	if(acymailing_level(3) && acymailing_isAllowed($this->config->get('acl_newsletters_inbox_actions','all'))) include(dirname(__FILE__).DS.'inboxactions.php');
	echo $this->tabs->endPane(); ?>
	</div>
<?php } ?>
