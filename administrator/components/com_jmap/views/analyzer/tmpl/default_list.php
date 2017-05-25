<?php 
/** 
 * @package JMAP::ANALYZER::administrator::components::com_jmap
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
			<td align="left" width="65%"></td>
			<td>
				<?php
				echo $this->lists['type'];
				echo $this->pagination->getLimitBox();
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist analyzerlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_JMAP_ANALYZER_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'analyzer.display'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_COMPONENT'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_MENUTITLE'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_MENUID'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_LASTMOD'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_CHANGEFREQ'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_ANALYZER_PRIORITY'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_JMAP_ANALYZER_VALID'); ?>
			</th>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1)):?>
				<th class="title">
					<?php echo JText::_('COM_JMAP_ANALYZER_INDEXED'); ?>
				</th>
			<?php endif;?>
			<th class="title">
				<?php
					if($this->validationType == 2):
						echo JHTML::_('grid.sort', 'COM_JMAP_ANALYZER_STATUS_CODE', 'httpstatus', @$this->orders['order_Dir'], @$this->orders['order'], 'analyzer.display');
					else:
						echo JText::_('COM_JMAP_ANALYZER_STATUS_CODE');
					endif;
				?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	foreach ( $this->items as $row ) {
		// Only valid if the validation mode of the Analyzer is the default one
		if(isset($row->httpstatus)) {
			// Apply filtering if any
			if($this->link_type && $row->httpstatus != $this->link_type) {
				continue;
			}
			
			// Manage semaphore icons
			$linkValidation = null;
			switch ((int)$row->httpstatus) {
				case (int)$row->httpstatus > 200 && (int)$row->httpstatus < 400:
					$statusAlt = JText::_('COM_JMAP_ANALYZER_LINKVALID_REDIRECT');
					$valid = JHtml::image('admin/publish_y.png', $statusAlt, 'class="hasTooltip" title="' . $statusAlt . '"', true);
					$linkValidation = false;
					break;
	
				case (int)$row->httpstatus > 400;
					$statusAlt = JText::_('COM_JMAP_ANALYZER_LINK_NOVALID');
					$valid = JHtml::image('admin/publish_x.png', $statusAlt, 'class="hasTooltip" title="' . $statusAlt . '"', true);
					$linkValidation = false;
				break;
	
				case (int)$row->httpstatus == 200:
				default:
					$statusAlt = JText::_('COM_JMAP_ANALYZER_LINKVALID');
					$valid = '<img class="hasTooltip" title="' . $statusAlt . '" alt="' . $statusAlt . '" src="' . JURI::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" />';
					$linkValidation = true;
				break;
			}
		} else {
			$valid = '<img data-role="validation_status" class="hasTooltip" title="' . JText::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" alt="' . JText::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" src="' . JURI::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16" border="0" />';
			$linkValidation = true;
		}

		// Manage waiter for indexing status JS app
		$indexingWaiter = '<img data-role="indexing_status" class="hasTooltip" title="' . JText::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" alt="' . JText::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" src="' . JURI::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16" border="0" />';
		?>
		<tr>
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td>
				<a data-role="<?php echo $linkValidation ? $this->dataRole : 'neutral';?>" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank">
					<?php echo $row->loc; ?>
					<span class="glyphicon glyphicon-share"></span>
				</a>
			</td>
			<td class="hidden-phone">
				<?php echo str_replace('com_', '', $row->component); ?>
			</td>
			<td class="hidden-phone">
				<?php echo $row->menuTitle; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $row->menuId; ?>
			</td>
			<td class="hidden-phone">
				<?php echo isset($row->lastmod) ? $row->lastmod : '-'; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $row->changefreq; ?>
			</td>
			<td class="hidden-phone">
				<?php echo (floatval($row->priority) * 100) . '%'; ?>
			</td>
			<td>
				<?php echo $valid; ?>
			</td>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1)):?>
				<td>
					<?php echo $linkValidation ? $indexingWaiter : $valid; ?>
				</td>
			<?php endif; ?>
			<td>
				<?php echo isset($row->httpstatus) ? ($row->httpstatus == '200' ? $row->httpstatus : '<span class="errorcode">' . $row->httpstatus . '</span>') : '<span class="httpcode"></span>'; ?>
			</td>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( JText::_('COM_JMAP_ANALYZER_NOLINKS_ONTHISPAGE') );
	}
	?>
	<tfoot>
		<td colspan="100%">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="analyzer.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>