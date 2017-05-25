<?php 
/** 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
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
				
				<div class="clr vspacer"></div>
				
				<span class="input-group double active">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span> <?php echo JText::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>:</span>
				  <input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" value="<?php echo $this->dates['from'];?>" class="text_area"/>
				</span>
				<span class="input-group double active">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span> <?php echo JText::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>:</span>
				  <input type="text" name="toperiod" id="toPeriod" data-role="calendar" value="<?php echo $this->dates['to'];?>" class="text_area"/>
				</span>
				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-xs" onclick="document.getElementById('fromPeriod').value='';document.getElementById('toPeriod').value='';this.form.submit();"><?php echo JText::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<a href="#pingomatic_stats" title="<?php echo JText::_('COM_JMAP_TITLE_PINGOMATIC_STATS');?>" class="fancybox btn btn-primary btn-xs absolute hasTooltip hidden-phone"><span class="glyphicon glyphicon-stats"></span> <?php echo JText::_( 'COM_JMAP_OPEN_PINGOMATIC_STATS' ); ?></a>
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
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_LINKTITLE', 's.title', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_LINKURL', 's.blogurl', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_LINKRSS', 's.rssurl', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_LASTPING', 's.lastping', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th width="1%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=pingomatic.editEntity&cid[]='. $row->id ;
		
		if($this->user->authorise('core.edit', 'com_jmap')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ? JHtml::_('jgrid.checkedout', $i, '', $row->checked_out_time, 'pingomatic.', false) : JHtml::_('grid.id', $i, $row->id);
		} else {
			$checked = null;
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
					echo $row->title;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JMAP_EDIT_LINK' ); ?>">
						<?php echo $row->title; ?></a>
					<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $row->blogurl; ?>
			</td>
			<td class="hidden-phone" align="center">
				<?php echo $row->rssurl; ?>
			</td>
			 
			<td class="hidden-phone" align="center">
				<?php echo $row->lastping;?>
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
	<input type="hidden" name="task" value="pingomatic.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>

<iframe class="notvisible fancybox" id="pingomatic_stats"  width="660px" height="420px" src="index.php?option=com_jmap&amp;task=pingomatic.display&amp;format=raw"></iframe>