<?php 
/** 
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage views
 * @subpackage wizard
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<div id="accordion_cpanel_icons">
	<div class="panel panel-warning">
		<div class="panel-heading"><h4><?php echo JText::_('COM_JMAP_AVAILABLE_EXTENSIONS_DATASOURCES')?></h4></div>
		<div class="panel-body">
	    	<?php echo $this->icons; ?>
	  	</div>
	</div>
</div>

<div id="accordion_cpanel_icons">
	<div class="panel panel-info">
		<div class="panel-heading"><h4><?php echo JText::_('COM_JMAP_CUSTOM_DATASOURCE')?></h4></div>
		<div class="panel-body">
	    	<?php echo $this->customIcon; ?>
	    	<?php echo $this->pluginIcon; ?>
	    	<?php echo $this->linksIcon; ?>
	  	</div>
	</div>
</div>

<form name="adminForm" id="adminForm" action="index.php">
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value=""/>
</form>