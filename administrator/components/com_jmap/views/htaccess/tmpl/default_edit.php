<?php 
/** 
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<style type="text/css">
#system-message-container {position: absolute;right: 5px;top: 5px;}
#system-message-container dl, #system-message-container ul{margin: 0;padding: 0;}
</style>
<!-- EDITOR ICONS -->
<form id="adminForm" name="adminForm" class="iframeform" action="index.php" method="post">
	<button id="htaccess_save" class="btn btn-primary active"><span class="glyphicon glyphicon-floppy-disk"></span>
		<?php echo $this->htaccessVersion ? JText::_('COM_JMAP_HTACCESS_TEXTUAL_SAVE') : JText::_('COM_JMAP_HTACCESS_SAVE');?>
	</button>
	<button id="htaccess_prev_versioning" class="btn btn-warning active">
		<span class="glyphicon glyphicon-chevron-left"></span>
		<?php echo JText::_('COM_JMAP_HTACCESS_PREV');?>
		<span class="label label-primary" data-bind="versions_counter">0</span>
	</button>
	<button id="htaccess_restore" class="btn btn-warning active"><span class="glyphicon glyphicon-retweet"></span>
		<?php echo JText::_('COM_JMAP_HTACCESS_RESTORE');?>
	</button>
	<button id="fancy_closer" class="btn btn-default active"><span class="glyphicon glyphicon-remove"></span>
		<?php echo JText::_('COM_JMAP_HTACCESS_CLOSE');?>
	</button>
	<?php if($this->htaccessVersion):?>
		<label data-content="<?php echo JText::_('COM_JMAP_HTACCESS_TEXTUAL_WARNING_DESC');?>" data-placement="bottom" class="label label-warning hasHtaccessPopover">
		<span class="glyphicon glyphicon-warning-sign"></span>
			<?php echo JText::_('COM_JMAP_HTACCESS_TEXTUAL_WARNING');?>
		</label>
	<?php endif;?>
	
	<div id="htaccess_controls">
		<select id="htaccess_directive">
			<option data-type="path" data-directive="301" value="301pagefile"><?php echo JText::_('COM_JMAP_HTACCESS_301_PAGEFILE');?></option>
			<option data-type="folder" data-directive="301" value="301pathfolder"><?php echo JText::_('COM_JMAP_HTACCESS_301_PATHFOLDER');?></option>
			<option data-type="path" data-directive="404" value="404pagefile"><?php echo JText::_('COM_JMAP_HTACCESS_404_PAGEFILE');?></option>
			<option data-type="folder" data-directive="404" value="404pathfolder"><?php echo JText::_('COM_JMAP_HTACCESS_404_PATHFOLDER');?></option>
		</select>
		<button id="htaccess_adder" class="btn btn-success active"><span class="glyphicon glyphicon-plus"></span>
			<?php echo JText::_('COM_JMAP_HTACCESS_ADD');?>
		</button>
		
		<div class="paths">
			<label data-role="basic" data-content="<?php echo JText::_('COM_JMAP_HTACCESS_PATH_DESC');?>" class="label label-primary hasrightPopover"><?php echo JText::_('COM_JMAP_HTACCESS_OLD_PATH');?></label>
			<input data-role="basic" id="path1" type="text" value=""/>
			
			<label data-role="extended" data-content="<?php echo JText::_('COM_JMAP_HTACCESS_PATH_DESC');?>" class="label label-primary hasrightPopover"><?php echo JText::_('COM_JMAP_HTACCESS_NEW_PATH');?></label>
			<input data-role="extended" id="path2" type="text" value=""/>
		</div>
	</div>
	
	<textarea id="htaccess_contents" name="htaccess_contents"><?php echo $this->record;?></textarea>
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value="htaccess.saveEntity"/>
	<input type="hidden" name="restored" value="0"/>
	<input type="hidden" name="tmpl" value="component"/>
</form>
