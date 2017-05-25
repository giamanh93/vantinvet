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
<div id="accordion_datasource_details" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_details"><h4><?php echo JText::_('COM_JMAP_DETAILS' ); ?></h4></div>
	<div class="panel-body panel-collapse collapse" id="datasource_details">
		<table class="admintable">
		<tbody>
			<tr>
				<td class="key left_title">
					<label for="title">
						<?php echo JText::_('COM_JMAP_NAME' ); ?>:
					</label>
				</td>
				<td class="right_details">
					<?php $readOnly = in_array($this->record->type, array('menu', 'plugin')) ? 'readonly="readonly"' : '';?>
					<input class="inputbox" type="text" <?php echo $readOnly;?> name="name" id="name" data-validation="required" size="50" value="<?php echo $this->record->name;?>" />
				</td>
			</tr>
			<tr>
				<td class="key left_title">
					<label for="type">
						<?php echo JText::_('COM_JMAP_TYPE' ); ?>:
					</label>
				</td>
				<td class="right_details">
					<input class="inputbox" type="text" readonly="readonly" name="type" id="type" size="50" value="<?php echo $this->record->type;?>" />
				</td>
			</tr> 
			<tr>
				<td class="key left_title">
					<label for="description">
						<?php echo JText::_('COM_JMAP_DESCRIPTION' ); ?>:
					</label>
				</td>
				<td class="right_details">
					<?php $readOnly = in_array($this->record->type, array('plugin')) && !$this->record->id ? 'readonly="readonly"' : '';?>
					<textarea class="inputbox" <?php echo $readOnly;?> name="description" id="description" rows="5" cols="80" ><?php echo $this->record->description;?></textarea>
				</td>
			</tr> 
			<tr>
				<td class="key left_title">
					<label for="description">
						<?php echo JText::_('COM_JMAP_PUBLISHED' ); ?>:
					</label>
				</td>
				<td class="right_details">
					<fieldset class="radio btn-group">
						<?php echo $this->lists['published']; ?>
					</fieldset>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
</div>