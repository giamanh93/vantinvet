<?php 
/** 
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
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
<!-- CPANEL ICONS -->
<form id="adminForm" name="adminForm" class="iframeform" action="index.php" method="post">
	<button class="btn btn-primary active"><span class="glyphicon glyphicon-floppy-disk"></span>
		<?php echo $this->robotsVersion ? JText::_('COM_JMAP_ROBOTS_DIST_SAVE') : JText::_('COM_JMAP_ROBOTS_SAVE');?>
	</button>
	<button id="fancy_closer" class="btn btn-default active"><span class="glyphicon glyphicon-remove"></span>
		<?php echo JText::_('COM_JMAP_ROBOTS_CLOSE');?>
	</button>
	<?php if($this->robotsVersion):?>
		<label data-content="<?php echo JText::_('COM_JMAP_ROBOTS_DIST_WARNING_DESC');?>" data-placement="bottom" class="label label-warning hasRobotsPopover">
		<span class="glyphicon glyphicon-warning-sign"></span>
			<?php echo JText::_('COM_JMAP_ROBOTS_DIST_WARNING');?>
		</label>
	<?php endif;?>
	
	<div id="robots_ctrls">
		<label class="label label-primary"><?php echo JText::_('COM_JMAP_ROBOTS_CHOOSE_RULE');?></label>
		<select id="robots_rule">
			<option value="Disallow: ">Disallow</option>
			<option value="Allow: ">Allow</option>
			<option value="User-agent: ">User agent</option>
		</select>
		<input id="robots_entry" type="text" value=""/>
		<button id="robots_adder" class="btn btn-success active"><span class="glyphicon glyphicon-plus"></span>
			<?php echo JText::_('COM_JMAP_ROBOTS_ADD');?>
		</button>
	</div>
	
	<textarea id="robots_contents" name="robots_contents"><?php echo $this->record;?></textarea>
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value="cpanel.saveEntity"/>
	<input type="hidden" name="tmpl" value="component"/>
</form>
