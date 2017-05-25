<?php // no direct access
defined('_JEXEC') or die('Restricted access');
vmJsApi::jQuery();
vmJsApi::chosenDropDowns();
?>

<!-- Currency Selector Module -->
<?php echo $text_before; ?>
<?php 
	$this_name_form = 'user_mode'.$module->id; 
	$class = "class='inputbox selectpicker' OnChange='".$this_name_form.".submit();return false;'";	
?>
<div id="curVm3_<?php echo $module->id; ?>" class="curVm3">
	<form id="cur_form" class="cur_box" name="user_mode<?php echo $module->id; ?>" action="<?php echo vmURI::getCleanUrl(); ?>" method="post"> 
		<!-- <input class="button" type="submit" name="submit" value="<?php echo vmText::_('MOD_VIRTUEMART_CURRENCIES_CHANGE_CURRENCIES') ?>" /> -->
		<?php echo JHTML::_('select.genericlist', $currencies, 'virtuemart_currency_id', $class , 'virtuemart_currency_id', 'currency_txt', $virtuemart_currency_id) ; ?>
	</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$modId = $('#curVm3_<?php echo $module->id; ?>');
	// Selectpicker
	$('.selectpicker', $modId).selectpicker();	
	$('.bootstrap-select', $modId).bind("hover", function() {		
		$(this).children(".dropdown-menu").stop().slideToggle(0);		
	}, function(){
		$(this).children(".dropdown-menu").stop().slideToggle(0);
	});
});
</script>