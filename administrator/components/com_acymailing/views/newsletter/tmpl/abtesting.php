<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" class="abTestingPage">
	<?php
	if(empty($this->mailid) && empty($this->validationStatus)){
		acymailing_display(JText::_('PLEASE_SELECT_NEWSLETTERS'),'warning');
		return;
	}
	if(!empty($this->missingMail)) return;
	if($this->validationStatus == 'abTestFinalSend') return; ?>

	<fieldset>
		<div class="acyheader icon-48-acyabtesting" style="float: left;"><?php echo $this->titlePage; ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table>
				<tr>
					<?php if(empty($this->noButton)){ ?>
					<td><a onclick="javascript:if(confirm('<?php echo JText::_('PROCESS_CONFIRMATION',true);?>')){submitbutton('abtest');} return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('ABTESTING_TEST',true); ?>"></span><?php echo JText::_('ABTESTING_TEST'); ?></a></td>
					<?php } ?>
				</tr>
			</table>
		</div>
	</fieldset>
	<div id="iframedoc"></div>
	<script type="text/javascript">
	function updateReceivers(prct){
		newVal= Math.floor(prct.value*<?php echo $this->nbTotalReceivers; ?>/100);
		document.getElementById('nbtestreceivers').innerHTML=newVal;
	}
	</script>
	<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=newsletter" method="post" name="adminForm"  id="adminForm" autocomplete="off">
		<input type="hidden" name="mailid" value="<?php echo $this->mailid; ?>" />
		<input type="hidden" name="tmpl" value="component" />
		<fieldset>
			<?php echo JText::sprintf('ABTESTING_PART_RECEIVER', '<input type="text" id="abTesting_prct" name="abTesting_prct" style="width:20px;" value="'.$this->abTestDetail['prct'].'" oninput="updateReceivers(this)">%'); ?>
			<div class="abtesting_mails">
				<table class="adminlist">
					<?php
					echo '<thead><tr><th width="45%">'.JText::_('NEWSLETTER').'</th>';
					if(!empty($this->savedValues)){
						echo '<th>'.JText::_('OPEN').'</th><th>'.JText::_('CLICKED_LINK').'</th><th>'.JText::_('ACY_SENT_EMAILS').'</th>';
						if(!empty($this->abTestDetail['status']) && $this->abTestDetail['status'] == 'testSendOver' && $this->validationStatus != 'abTestAdd' && $this->abTestDetail['action'] == 'manual') echo '<th>Send</th>';
					}
					echo '</tr></thead>';
					foreach($this->mailsdetails as $oneMail){
						echo '<tr><td>'.$oneMail->subject.'</td>';
						if(!empty($this->savedValues)){
							$open = (!empty($this->statMail[$oneMail->mailid])?$this->statMail[$oneMail->mailid]->openunique:'0');
							$click = (!empty($this->statMail[$oneMail->mailid])?$this->statMail[$oneMail->mailid]->clickunique:'0');
							$sent = (!empty($this->statMail[$oneMail->mailid])?$this->statMail[$oneMail->mailid]->senthtml+$this->statMail[$oneMail->mailid]->senttext:'0');
							if($sent != 0){
								$openPrct = (!empty($this->statMail[$oneMail->mailid])?round($this->statMail[$oneMail->mailid]->openunique/$sent*100):'0');
								$clickPrct = (!empty($this->statMail[$oneMail->mailid])?round($this->statMail[$oneMail->mailid]->clickunique/$sent*100):'0');
							} else{
								$openPrct = 0;
								$clickPrct = 0;
							}
							echo '<td style="text-align:center">'.$open.' ('.$openPrct.'%)</td><td style="text-align:center">'.$click.' ('.$clickPrct.'%)</td><td style="text-align:center">'.$sent.'</td>';
						}
						if(!empty($this->abTestDetail['status']) && $this->abTestDetail['status'] == 'testSendOver' && $this->validationStatus != 'abTestAdd' && $this->abTestDetail['action'] == 'manual'){
							echo '<td><a href="index.php?option=com_acymailing&ctrl=newsletter&task=complete_abtest&tmpl=component&mailToSend='.$oneMail->mailid.'">Send</a></td>';
						}
						echo '</tr>';
					}?>
				</table>
			</div>
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'NEWSLETTER_SENT_TO' ); ?></legend>
				<table class="adminlist table table-striped" cellspacing="1" align="center">
					<tbody>
						<?php if(!empty($this->lists)){
							$k = 0;
							$listids = array();
							foreach($this->lists as $row){
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								if(!$row->published) echo '<a href="index.php?option=com_acymailing&ctrl=list&task=edit&listid='.$row->listid.'" title="'.JText::_('LIST_PUBLISH',true).'"><img style="margin:0px;" src="'.ACYMAILING_IMAGES.'warning.png" alt="Warning" /></a> ';
								echo acymailing_tooltip($row->description,$row->name,'',$row->name);
								echo ' ( '.JText::sprintf('SELECTED_USERS',$row->nbsub).' )';
								echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
								?>
							</td>
						</tr>
						<?php $k = 1-$k;}}else{ ?>
							<tr>
								<td>
									<?php echo JText::_('EMAIL_AFFECT');?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php
				if(!empty($this->mailReceiver->filter)){
					$resultFilters = $this->filterClass->displayFilters($this->mailReceiver->filter);
					if(!empty($resultFilters)){
						echo '<br />'.JText::_('RECEIVER_LISTS').'<br />'.JText::_('FILTER_ONLY_IF');
						echo '<ul><li>'.implode('</li><li>',$resultFilters).'</li></ul>';
					}
				}

				if(!empty($this->lists)){
				?>
				<div style="text-align:center;font-size:14px;padding-top:10px;margin:10px 30px;border-top: 1px solid #ccc;">
					<?php

						echo JText::sprintf('ABTESTING_SENTTO_NUMBER','<span style="font-weight:bold;" id="nbtestreceivers" >'.$this->nbTestReceivers.'</span>', '<span style="font-weight:bold;" id="nbreceivers" >'.$this->nbTotalReceivers.'</span>');
					?>
				</div>
				<?php } ?>
			</fieldset>
			<?php echo JText::sprintf('ABTESTING_MODIFY_RECEIVERS', '<a target="_blank" href="'.acymailing_completeLink(($this->app->isAdmin() ? '' : 'front').'newsletter&task=edit&mailid='.$this->mailsdetails[0]->mailid).'">'.$this->mailsdetails[0]->subject.'</a>'); ?>
		</fieldset>
		<fieldset>
			<?php echo JText::sprintf('ABTESTING_DELAY_ACTION', '<input type="text" id="abTesting_delay" name="abTesting_delay" style="width:20px;" value="'.$this->abTestDetail['delay'].'">'); ?>
			<div class="abtesting_actions">
				<input type="radio" name="abTesting_action" id="abTesting_action_manual" value="manual" <?php echo ($this->abTestDetail['action']=='manual')?'checked="checked"':'';?>><label for="abTesting_action_manual" class="radiobtn"><?php echo JText::_('DO_NOTHING');?></label><br />
				<input type="radio" name="abTesting_action" id="abTesting_action_open" value="open" <?php echo ($this->abTestDetail['action']=='open')?'checked="checked"':'';?>><label for="abTesting_action_open" class="radiobtn"><?php echo JText::_('ABTESTING_ACTION_GENERATE_OPEN');?></label><br />
				<input type="radio" name="abTesting_action" id="abTesting_action_click" value="click" <?php echo ($this->abTestDetail['action']=='click')?'checked="checked"':'';?>><label for="abTesting_action_click" class="radiobtn"><?php echo JText::_('ABTESTING_ACTION_GENERATE_CLICK');?></label><br />
				<input type="radio" name="abTesting_action" id="abTesting_action_mix" value="mix" <?php echo ($this->abTestDetail['action']=='mix')?'checked="checked"':'';?>><label for="abTesting_action_mix" class="radiobtn"><?php echo JText::_('ABTESTING_ACTION_GENERATE_MIX');?></label>
			</div>
		</fieldset>
		<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
		<input type="hidden" name="task" value="abtesting" />
		<input type="hidden" name="ctrl" value="newsletter" />
		<input type="hidden" name="nbTotalReceivers" value="<?php echo $this->nbTotalReceivers; ?>" />
	</form>
</div>
