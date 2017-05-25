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
?> 
<form action="index.php" method="post"  id="adminForm"  name="adminForm">  
	<?php 
	//API nuova JForm da config.xml con fields personalizzati in sostituzione di J Element 	  
	$fieldSets = $this->params_form->getFieldsets();
	$tabs = array();
	$contents = array();
	foreach ($fieldSets as $name => $fieldSet) :
		$label = JText::_(empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label); 
		$activeTab = $this->app->input->getString('jmap_tab_config', 'preferences');
		$activeClass = $fieldSet->id === $activeTab ? 'class="active"' : null;
		$activeClassContent = $fieldSet->id === $activeTab ? 'class="tab-pane active"' : 'class="tab-pane"';
		$tabs[] = "<li $activeClass><a href='#$fieldSet->id' data-toggle='tab' data-element='$fieldSet->id'>$label</a></li>";
		ob_start(); ?>
		<div <?php echo $activeClassContent;?> id="<?php echo $fieldSet->id;?>" class="tab-pane">
		<?php  
		foreach ($this->params_form->getFieldset($name) as $field):
		$fieldClass = $field->class != 'btn-group' ? trim(str_replace(array('label', 'label-info', 'btn-group', '-info'), '', $field->class)) : null;
		if($field->type == 'Spacer' && stripos($fieldClass, 'spacer') === false) {
			$fieldClass .= 'spacer';
		}
		?>
			<div class="control-group <?php echo $fieldClass;?>">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
		</div>
		<?php $contents[] = ob_get_clean();?>
	<?php endforeach; ?>
	
	<ul id="tab_configuration" class="nav nav-tabs"><?php echo implode('', $tabs);?></ul>
	<div id="config-jmap" class="tab-content"><?php echo implode('', $contents);?></div> 
	<input type="hidden" name="option" value="<?php	echo $this->option;?>" /> 
	<input type="hidden" name="task" value="" />
</form> 