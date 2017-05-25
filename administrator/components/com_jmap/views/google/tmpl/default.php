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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php echo $this->googleData;?>
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="gaperiod" id="gaperiod" value="" />
	<input type="hidden" name="gaquery" id="gaquery" value="" />
	<input type="hidden" name="task" value="google.display" />
</form>