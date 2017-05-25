<?php 
/** 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

// Ordering drag'n'drop management
if ($this->orders['order'] == 's.ordering') {
	$saveOrderingUrl = 'index.php?option=com_jmap&task=sources.saveOrder&format=json&ajax=1';
	JHtml::_('sortablelist.sortable', 'adminList', 'adminForm', strtolower($this->orders['order_Dir']), $saveOrderingUrl);
	$this->document->addScript ( JUri::root ( true ) . '/administrator/components/com_jmap/js/sortablelist.js', 'text/javascript', true );
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td align="left" width="65%">
				<span class="input-group">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span> <?php echo JText::_('COM_JMAP_FILTER' ); ?>:</span>
				  <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-xs" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td>
				<?php
				echo $this->lists['state'];
				echo $this->lists['type'];
				echo $this->pagination->getLimitBox();
				?>
			</td>
		</tr>
	</table>

	<table id="adminList" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th style="width:1%">
				<input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_TYPE', 's.type', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JMAP_DESCRIPTION'); ?>
			</th>
			<th class="order hidden-phone">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ORDER', 's.ordering', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
				<?php 
					if(isset($this->orders['order']) && $this->orders['order'] == 's.ordering'):
						echo JHTML::_('grid.order',  $this->items, 'filesave.png', 'sources.saveOrder'); 
					endif;
				 ?>
			</th>
			<th style="width:5%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display' ); ?>
			</th>
			<th style="width:5%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=sources.editEntity&cid[]='. $row->id ;

		if($this->user->authorise('core.edit.state', 'com_jmap')) {
			$taskPublishing	= !$row->published ? 'sources.publish' : 'sources.unpublish';
			$altPublishing 	= !$row->published ? JText::_( 'Publish' ) : JText::_( 'Unpublish' );
			$published = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? '<img alt="' . $altPublishing . '" src="' . JURI::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
			$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
			$published = $row->published ? '<img alt="' . $altPublishing . '" src="' . JURI::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
		}
		
		// Check for the links type data source language
		if($row->type == 'links' || $row->type == 'user') {
			$dataSourceParams = json_decode($row->params);
			if(isset($dataSourceParams->datasource_language) && $dataSourceParams->datasource_language != '*') {
				$published .= '<img style="margin-left:10px" src="' . JUri::root(false) . 'media/mod_languages/images/' . $dataSourceParams->datasource_language . '.gif" alt="language_flag" />';
			}
		}
		
		$checked = null;
		if($row->type == 'user' || $row->type == 'plugin' || $row->type == 'links' || ($row->type == 'content' && $this->cParams->get('multiple_content_sources', 0))) {
			if($this->user->authorise('core.edit', 'com_jmap')) {
				$checked = $row->checked_out && $row->checked_out != $this->user->id ? JHtml::_('jgrid.checkedout', $i, '', $row->checked_out_time, 'sources.', false) : JHtml::_('grid.id', $i, $row->id);
			} else {
				$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
			}
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
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JMAP_EDIT_SOURCE' ); ?>">
						<?php echo $row->name; ?></a>
					<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $row->type; ?>
			</td>
			<td class="hidden-phone" align="center">
				<?php echo $row->description; ?>
			</td>
			
			<td class="order hidden-phone">
				<?php 
				$ordering = $this->orders['order'] == 's.ordering'; 
				$disabled = $ordering ?  '' : 'disabled="disabled"'; 
				
				$iconClass = '';
				if (!$this->user->authorise('core.edit', 'com_jmap')) {
					$iconClass = ' inactive';
				}
				elseif (!$ordering) {
					$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
				}
				?>
				<div style="display:inline-block" class="sortable-handler<?php echo $iconClass ?>">
					<span class="icon-menu"></span>
				</div>
				
				<span class="moveup"><?php echo $this->pagination->orderUpIcon( $i, true, 'sources.moveorder_up', 'COM_JMAP_MOVE_UP', $ordering); ?></span>
				<span class="movedown"><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'sources.moveorder_down', 'COM_JMAP_MOVE_DOWN', $ordering); ?></span>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"  <?php echo $disabled; ?>  class="ordering_input" style="text-align: center" />
			</td>
					
			<td align="center">
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

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="sources.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>