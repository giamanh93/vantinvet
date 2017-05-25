<?php 
/** 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<div id="accordion_datasource_excludemenu" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_excludemenu"><h4><?php echo JText::_('COM_JMAP_MENU_EXCLUSION' ); ?></h4></div>
	<div class="panel-body panel-collapse collapse" id="datasource_excludemenu">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_CHOOSE_MENU_EXCLUSION_DESC');?>"><?php echo JText::_('COM_JMAP_CHOOSE_MENU_EXCLUSION');?></label></span>
				</td>
				<td class="paramlist_value">
					<?php echo $this->lists['exclusion']; ?>
				</td>
			</tr>
		</table>
	</div>
</div>

<div id="accordion_datasource_menupriorities" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_menupriorities"><h4><?php echo JText::_('COM_JMAP_MENU_PRIORITIES' ); ?></h4></div>
	<div class="panel-body panel-collapse collapse" id="datasource_menupriorities">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_ASSIGN_MENU_PRIORITIES_DESC');?>"><?php echo JText::_('COM_JMAP_ASSIGN_MENU_PRIORITIES');?></label></span>
				</td>
				<td class="paramlist_value">
					<?php echo $this->lists['menu_priorities']; ?>
					<?php echo $this->lists['priorities']; ?>
					<div id="controls_grouper">
						<button data-role="priority_action" data-action="store" data-type="MenuPriorities" class="btn btn-xs btn-primary active"><span class="glyphicon glyphicon-floppy-disk"></span><?php echo JText::_('COM_JMAP_ASSIGN_MENU_PRIORITIES_BTN');?></button>
						<button data-role="priority_action" data-action="remove" data-type="MenuPriorities" class="btn btn-xs btn-default active"><span class="glyphicon glyphicon-remove"></span><?php echo JText::_('COM_JMAP_REMOVE_MENU_PRIORITIES_BTN');?></button>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>