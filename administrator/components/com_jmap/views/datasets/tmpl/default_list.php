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
	<table class="full headerlist">
		<tr>
			<td align="left" width="80%">
				<span class="input-group">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span> <?php echo JText::_('COM_JMAP_FILTER' ); ?>:</span>
				  <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-xs" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->pagination->getLimitBox(); ?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th width="1%">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th width="1%">
				<input type="checkbox" name="toggle" value=""  onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" width="20%">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_DATASET_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_JMAP_DATASET_DESC'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_JMAP_DATASET_DATASOURCES'); ?>
			</th>
			<th style="width:5%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
			<th width="1%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=datasets.editEntity&cid[]='. $row->id ;
		
		// Access check.
		if($this->user->authorise('core.edit.state', 'com_jmap')) {
			$taskPublishing	= !$row->published ? 'datasets.publish' : 'datasets.unpublish';
			$altPublishing 	= !$row->published ? JText::_( 'Publish' ) : JText::_( 'Unpublish' );
			$published = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? '<img alt="' . $altPublishing . '" src="' . JURI::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
			$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
			$published = $row->published ? '<img alt="' . $altPublishing . '" src="' . JURI::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
		}
		
		$checked = null;
		// Access check.
		if($this->user->authorise('core.edit', 'com_jmap')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ? JHtml::_('jgrid.checkedout', $i, '', $row->checked_out_time, 'datasets.', false) : JHtml::_('grid.id', $i, $row->id);
		} else {
			$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr>
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->get ('id'))) || !$this->user->authorise('core.edit', 'com_jmap') ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JMAP_EDIT_DATASET' ); ?>">
						<?php echo $row->name; ?></a>
					<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $row->description; ?>
			</td>
			<td align="center">
				<?php
					$wrappedNames = array_map(
						function ($el) {
							return "<label class='label label-primary label-sources'>$el</label>";
						},
						$row->sourcesNames
					);
					echo implode('', $wrappedNames);
				?>
			</td>
			<td>
				<?php echo $published;?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
	<tfoot>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="datasets.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>