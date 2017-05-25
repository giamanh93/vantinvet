<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" >
<div id="iframedoc"></div>
<form action="<?php echo JRoute::_('index.php?option='.ACYMAILING_COMPONENT); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm" >
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
	echo JHTML::_( 'form.token' ); ?>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'IMPORT_FROM' ); ?></legend>
		<?php echo JHTML::_('acyselect.radiolist',   $this->importvalues, 'importfrom', 'class="inputbox" size="1" onclick="updateImport(this.value);"', 'value', 'text',JRequest::getCmd('importfrom','textarea')); ?>
	</fieldset>
	<div>
	<?php foreach($this->importdata as $div => $name){
		echo '<div id="'.$div.'"';
		if($div != JRequest::getCmd('importfrom','textarea')) echo ' style="display:none"';
		echo '>';
		echo '<fieldset class="adminform">';
		echo '<legend>'.$name.'</legend>';
		include(dirname(__FILE__).DS.$div.'.php');
		echo '</fieldset>';
		echo '</div>';
		}?>
	</div>
	<fieldset class="adminform" id="importlists">
		<legend><?php echo JText::_( 'IMPORT_SUBSCRIBE' ); ?></legend>
		<table class="adminlist table table-striped" cellpadding="1">
		<?php
		$currentValues = JRequest::getVar('importlists');
		$listid = JRequest::getInt('listid');
		$k = 0;
		foreach( $this->lists as $row){?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>'; ?>
					<?php
					$text = '<b>'.JText::_('ACY_ID').' : </b>'.$row->listid.'<br />'.$row->description;
					echo acymailing_tooltip($text, $row->name, 'tooltip.png', $row->name);
					?>
				</td>
				<td align="center">
					<?php if(!empty($row->campaign)){
						echo JHTML::_('acyselect.radiolist', $this->campaignValues[$row->listid], "importlists[".$row->listid."]", '', 'value', 'text', JRequest::getCmd('importlists['.$row->listid.']',$row->listid==JRequest::getInt('filter_lists')?2:0), "importlists".$row->listid."-");
					} else{
						echo JHTML::_('acyselect.booleanlist', "importlists[".$row->listid."]", '', !empty($currentValues[$row->listid]) || JRequest::getInt('filter_lists')==$row->listid || $listid==$row->listid, JText::_('JOOMEXT_YES'), JTEXT::_('JOOMEXT_NO'),"importlists".$row->listid."-");
					} ?>
				</td>
			</tr>
			<?php
			$k = 1-$k;
		}
		if(acymailing_isAllowed($this->config->get('acl_lists_manage','all'))){ ?>
		<tr class="<?php echo "row$k"; ?>" id="importcreatelist">
			<td colspan="2">
				<?php echo JText::_('IMPORT_SUBSCRIBE_CREATE').' : <input type="text" name="createlist" placeholder="'.JText::_('LIST_NAME').'" />'; ?>
			</td>
		</tr>
		<?php } ?>
		</table>
	</fieldset>
</form>
</div>
