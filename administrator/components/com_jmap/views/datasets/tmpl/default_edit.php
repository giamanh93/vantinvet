<?php 
/** 
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div id="accordion_datasets_details" class="panel panel-info panel-group adminform">
		<div class="panel-heading" data-target="#datasets_details"><h4><?php echo JText::_('COM_JMAP_DATASETS_DETAILS' ); ?></h4></div>
		<div class="panel-body panel-collapse collapse" id="datasets_details">
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="title">
								<?php echo JText::_('COM_JMAP_DATASET_NAME' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="name" id="name" data-validation="required" value="<?php echo $this->record->name;?>" />
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="type">
								<?php echo JText::_('COM_JMAP_DATASET_DESC' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<textarea class="inputbox" name="description" id="description" rows="5"><?php echo $this->record->description;?></textarea>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label>
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
	
	<div id="accordion_datasets_datasources" class="panel panel-info panel-group adminform">
		<div class="panel-heading" data-target="#datasets_datasources"><h4><?php echo JText::_('COM_JMAP_DATASETS_DATASOURCES' ); ?></h4></div>
		<div class="panel-body panel-collapse collapse" id="datasets_datasources">
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="title" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_CHOOSE_DATASOURCES_DESC');?>">
								<?php echo JText::_('COM_JMAP_CHOOSE_DATASOURCES' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<ol class="datasources_selectable">
								<?php foreach ($this->lists['sources'] as $index=>$source):
									$dataId = 'data-id="' . $source->id . '"';
									$selectedClass = in_array($source->id, json_decode($this->record->sources)) ? 'ui-selected' : '';
									
									// Check if data sources number exceed this ordered list
									if(($index > 0) && (($index % ($this->componentParams->get('selectable_limit_pagination', 10))) == 0)) {
										?>
										</ol>
										<ol class="datasources_selectable">
										<?php 
									}
								?>
								 	<li <?php echo $dataId;?> class="ui-widget-content <?php echo $selectedClass;?>">
								  		<?php echo $source->name;?>
								  	</li>
								<?php endforeach; ?>
							</ol>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" id="sources" name="sources" value="<?php echo $this->record->sources; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>