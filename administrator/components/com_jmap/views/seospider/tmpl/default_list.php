<?php 
/** 
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage analyzer
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td id="alert_append" align="left" width="65%">
				<span class="input-group">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span> <?php echo JText::_('COM_JMAP_FILTER_ONPAGE' ); ?>:</span>
				  <input type="text" name="searchpage" id="searchpage" value="<?php echo htmlspecialchars($this->searchpageword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-xs" onclick="document.getElementById('searchpage').value='';this.form.submit();"><?php echo JText::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td>
				<?php
				echo $this->pagination->getLimitBox();
				?>
			</td>
		</tr>
		<tr>
			<td colspan="100%">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</table>

	<table class="adminlist seospiderlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th style="width:15%" class="title">
				<?php echo JHTML::_('grid.sort', 'COM_JMAP_SEOSPIDER_CRAWLED_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'seospider.display'); ?>
			</th>
			<th style="width:2%" class="title hidden-tablet hidden-phone">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_STATUS'); ?>
			</th>
			<th style="width:10%" class="title">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_LINK_TITLE'); ?>
			</th>
			<th style="width:15%" class="title">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_LINK_DESC'); ?>
			</th>
			<th style="width:15%" class="title hidden-tablet hidden-phone">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_H1'); ?>
			</th>
			<th style="width:15%" class="title hidden-tablet hidden-phone">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_H2'); ?>
			</th>
			<th style="width:15%" class="title hidden-tablet hidden-phone">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_H3'); ?>
			</th>
			<th style="width:10%" class="title hidden-tablet hidden-phone">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_CANONICAL'); ?>
			</th>
			<th style="width:1%" class="title">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_TITLE_DUPLICATES'); ?>
			</th>
			<th style="width:1%" class="title">
				<?php echo JText::_('COM_JMAP_SEOSPIDER_DESC_DUPLICATES'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	foreach ( $this->items as $row ) {
		// Evaluate filtering by search page word
		if($this->searchpageword) {
			$isMatching = (stripos($row->loc, $this->searchpageword) !== false);
			if(!$isMatching) {
				continue;
			}
		}
		// Manage waiter for indexing status JS app
		$indexingWaiter = '<img class="hasTooltip" title="' . JText::_('COM_JMAP_SEOSPIDER_INDEXING_WAITER') . '" alt="' . JText::_('COM_JMAP_SEOSPIDER_INDEXING_WAITER') . '" src="' . JURI::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16" border="0" />';
		?>
		<tr>
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td>
				<a data-role="link" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank">
					<?php echo $row->loc; ?>
					<span class="glyphicon glyphicon-share"></span>
				</a>
			</td>
			<td class="hidden-tablet hidden-phone">
				<div data-bind="{status}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div data-bind="{title}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div data-bind="{desc}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="hidden-tablet hidden-phone">
				<div data-bind="{h1}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="hidden-tablet hidden-phone">
				<div data-bind="{h2}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="hidden-tablet hidden-phone">
				<div data-bind="{h3}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="hidden-tablet hidden-phone">
				<div data-bind="{canonical}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div class="trigger_dialog" data-bind="{title-duplicates}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div class="trigger_dialog" data-bind="{desc-duplicates}"><?php echo $indexingWaiter;?></div>
			</td>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( JText::_('COM_JMAP_SEOSPIDER_NOLINKS_ONTHISPAGE') );
	}
	?>
	</tbody>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="seospider.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>