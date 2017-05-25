<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminform" width="100%">
	<tr>
		<td class="key" id="subjectkey">
			<label for="subject">
				<?php echo JText::_( 'JOOMEXT_SUBJECT' ); ?>
			</label>
		</td>
		<td id="subjectinput">
			<input type="text" name="data[mail][subject]" id="subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->mail->subject); ?>" onClick="zoneToTag='subject';" />
		</td>
		<td class="key" id="publishedkey">
			<label for="published">
				<?php echo JText::_( 'ACY_PUBLISHED' ); ?>
			</label>
		</td>
		<td id="publishedinput">
			<?php echo ($this->mail->published == 2) ? JText::_('SCHED_NEWS') : JHTML::_('acyselect.booleanlist', "data[mail][published]" , '',$this->mail->published,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" id="aliaskey">
			<label for="alias">
				<?php echo JText::_( 'JOOMEXT_ALIAS' ); ?>
			</label>
		</td>
		<td id="aliasinput">
			<input class="inputbox" type="text" name="data[mail][alias]" id="alias" style="width:80%" value="<?php echo @$this->mail->alias; ?>" <?php echo ($this->type == 'joomlanotification'?'readonly':''); ?>/>
		</td>
	<?php if($this->type != 'joomlanotification'){ ?>
		<td class="key" id="visiblekey">
			<label for="visible">
				<?php echo JText::_( 'JOOMEXT_VISIBLE' ); ?>
			</label>
		</td>
		<td id="visibleinput">
			<?php echo JHTML::_('acyselect.booleanlist', "data[mail][visible]" , '',$this->mail->visible,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" id="picturekey" valign="top">
			<?php echo JText::_('ACY_THUMBNAIL'); ?>
		</td>
		<td id="pictureinput" valign="top">
			<?php $uploadpictType = acymailing_get('type.uploadpict'); echo $uploadpictType->display('data[mail][thumb]', 'thumb', $this->mail->thumb); ?>
		</td>
	<?php } ?>
		<td class="key" id="sendhtmlkey">
			<?php echo JText::_( 'SEND_HTML' ); ?>
		</td>
		<td id="sendhtmlinput">
			<?php echo JHTML::_('acyselect.booleanlist', "data[mail][html]" , 'onclick="updateAcyEditor(this.value); initTagZone(this.value);"',$this->mail->html,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<?php if($this->type != 'joomlanotification'){ ?>
			<tr>
				<td class="key" id="summarykey" valign="top">
					<label for="summaryfield">
						<?php echo JText::_('ACY_SUMMARY'); ?>
					</label>
				</td>
				<td id="summaryinput">
					<textarea placeholder="<?php echo JText::_('ACY_SUMMARY_PLACEHOLDER') ?>" style="width:80%;height:60px;" id="summaryfield" name="data[mail][summary]"><?php echo $this->escape(@$this->mail->summary); ?></textarea>
				</td>
				<td class="key" id="createdkey" valign="top">
					<?php echo JText::_( 'CREATED_DATE' ); ?>
				</td>
				<td id="createdinput" valign="top">
					<?php echo acymailing_getDate(@$this->mail->created);?>
				</td>
			</tr>
		<?php if(!empty($this->mail->senddate)){ ?>
				<tr>
					<td class="key" id="senddatekey">
						<?php echo JText::_( 'SEND_DATE' ); ?>
					</td>
					<td id="senddateinput">
						<?php echo acymailing_getDate(@$this->mail->senddate);?>
					</td>
					<td class="key" id="sentbykey">
						<?php if(!empty($this->mail->sentby)) echo JText::_( 'SENT_BY' ); ?>
					</td>
					<td id="sentbyinput">
						<?php echo @$this->sentbyname; ?>
					</td>
				</tr>
		<?php }
		}
		$jflanguages = acymailing_get('type.jflanguages');
		if($jflanguages->multilingue){
	?>
		<tr>
			<td class="key" id="languagekey">
				<label for="jlang">
					<?php echo JText::_('ACY_LANGUAGE'); ?>
				</label>
			</td>
			<td id="languageinput" colspan="3">
				<?php
					$jflanguages->sef = true;
					echo $jflanguages->displayJLanguages('data[mail][language]', empty($this->mail->language) ? '' : $this->mail->language);
				?>
			</td>
		</tr>
	<?php } ?>
</table>
