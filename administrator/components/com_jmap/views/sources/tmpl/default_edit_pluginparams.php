<?php 
/** 
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage views
 * @subpackage config
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
$fieldSets = $this->params_form->getFieldsets();
?>
<div id="accordion_datasource_plugin_parameters" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_plugin_parameters"><h4><?php echo JText::_('COM_JMAP_PLUGIN_CONFIGURATION' ); ?></h4></div>
	<div class="panel-body panel-collapse collapse" id="datasource_plugin_parameters">
		<table  class="admintable">
			<?php foreach ($fieldSets as $name => $fieldSet) :  ?>
				<?php  
				foreach ($this->params_form->getFieldset($name) as $field):
					?>
					<tr>
						<td class="paramlist_key left_title"><?php echo $field->label; ?></td>
						<td class="paramlist_value"><?php echo $field->input; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</table>
	</div>
</div>


