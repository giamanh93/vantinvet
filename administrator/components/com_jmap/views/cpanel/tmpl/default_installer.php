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

<!-- INSTALLER PROGRESS STEPS -->

<div class="progress">
	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
		<span class="step_details"><?php echo JText::_('COM_JMAP_INSTALL_STEP1');?></span>
  	</div>
</div>

<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
    	<span class="step_details"><?php echo JText::_('COM_JMAP_INSTALL_STEP2');?></span>
  	</div>
</div>

<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
    	<span class="step_details"><?php echo JText::_('COM_JMAP_INSTALL_STEP3');?></span>
  	</div>
</div>

<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
    	<span class="step_details"><?php echo JText::_('COM_JMAP_INSTALL_STEP4');?></span>
  	</div>
</div>

<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
    	<span class="step_details"><?php echo JText::_('COM_JMAP_INSTALL_STEP5');?></span>
  	</div>
</div>

<div class="alert alert-success hidden"><?php echo JText::_('COM_JMAP_INSTALL_FINAL');?></div>
