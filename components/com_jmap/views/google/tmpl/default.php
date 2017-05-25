<?php 
/** 
 * @package JMAP::OVERVIEW::administrator::components::com_jmap
 * @subpackage views
 * @subpackage overview
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
// title
if ( $this->cparams->get ( 'show_page_heading', 1 )) {
	$headerlevel = $this->cparams->get ( 'headerlevel', 1 );
	$title = $this->cparams->get ( 'page_heading', $this->menuTitle);
	echo '<h' . $headerlevel . '>' . $title . '</h' . $headerlevel . '>';
}
$cssClass = $this->cparams->get ( 'pageclass_sfx', null);
?>
<form action="<?php echo JRoute::_('index.php?option=com_jmap&view=google');?>" method="post" class="jes jesform <?php echo $cssClass;?>" id="adminForm" name="adminForm">
	<?php if($this->isLoggedIn):?>
		<div class="btn-toolbar well" id="toolbar">
			<div class="btn-wrapper pull-left" id="toolbar-download">
				<button onclick="JMapSubmitform('google.deleteEntity')" class="btn btn-primary btn-xs">
					<span class="glyphicon-lock"></span>
					<?php echo JText::_('COM_JMAP_GOOGLE_LOGOUT');?>
				</button>
			</div>
		</div>
	<?php endif; ?>
		
	<?php echo $this->googleData;?>
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="gaperiod" id="gaperiod" value="" />
	<input type="hidden" name="gaquery" id="gaquery" value="" />
	<input type="hidden" name="task" value="google.display" />
</form>