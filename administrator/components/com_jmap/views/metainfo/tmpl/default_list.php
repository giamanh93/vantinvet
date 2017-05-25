<?php 
/** 
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage views
 * @subpackage metainfo
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
				
				<span class="input-group-addon input-checkbox hasTooltip" title="<?php echo JText::_('COM_JMAP_METAINFO_EXACT_MATCH');?>">
					<input type="checkbox" id="exactsearchpage" name="exactsearchpage" value="1" <?php echo $this->exactsearchpage;?>/>
  					<label class="input-group" for="exactsearchpage"><?php echo JText::_('COM_JMAP_METAINFO_EXACT_MATCH_LABEL');?></label>
				</span>
			</td>
			<td>
				<?php
				echo $this->lists['excludestate'];
				echo $this->lists['state'];
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

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="metainfo.display" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
	
	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_JMAP_METAINFO_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'metainfo.display'); ?>
			</th>
			<th style="width:15%">
				<?php echo JText::_('COM_JMAP_METATITLE' ); ?>
			</th>
			<th style="width:15%">
				<?php echo JText::_('COM_JMAP_METADESC' ); ?>
			</th>
			<th style="width:10%">
				<?php echo JText::_('COM_JMAP_METAROBOTS' ); ?>
			</th>
			<th style="width:10%">
				<?php echo JText::_('COM_JMAP_METAIMAGE' ); ?>
			</th>
			<th style="width:15%">
				<?php echo JText::_('COM_JMAP_SAVE_DELETE' ); ?>
			</th>
			<th style="width:2%">
				<?php echo JText::_('COM_JMAP_STATUS' ); ?>
			</th>
			<th class="hidden-phone hidden-tablet" style="width:2%">
				<?php echo JText::_('COM_JMAP_EXCLUDED' ); ?>
			</th>
		</tr>
		
		<tr class="subtitles">
			<td style="width:1%">
			</td>
			<td class="title">
				<?php echo JText::_('COM_JMAP_SEARCH_ENGINES_LINK_DESC' ); ?>
			</td>
			<td style="width:15%">
				<?php echo JText::_('COM_JMAP_SEARCH_ENGINES_TITLE_DESC' ); ?>
			</td>
			<td style="width:15%">
				<?php echo JText::_('COM_JMAP_SEARCH_ENGINES_DESCRIPTION_DESC' ); ?>
			</td>
			<td style="width:10%">
				<?php echo JText::_('COM_JMAP_SEARCH_ENGINES_ROBOTS_DESC' ); ?>
			</td>
			<td style="width:10%">
				<?php echo JText::_('COM_JMAP_METAIMAGE_DESC' ); ?>
			</td>
			<td style="width:15%">
				<?php echo JText::_('COM_JMAP_SEARCH_ENGINES_SAVE_DELETE_DESC' ); ?>
			</td>
			<td style="width:2%">
				<?php echo JText::_('COM_JMAP_STATUS_DESC' ); ?>
			</td>
			<td class="hidden-phone hidden-tablet" style="width:2%">
				<?php echo JText::_('COM_JMAP_EXCLUDED_COMPLETELY_FROM_SITEMAP_DESC' ); ?>
			</td>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	foreach ( $this->items as $row ) {
		?>
		<tr>
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td class="link_loc">
				<a data-role="link" data-linkidentifier="<?php echo $k + 1;?>" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank" data-linkidentifier="<?php echo $k + 1;?>">
					<?php echo $row->loc; ?>
					<span class="glyphicon glyphicon-share"></span>
				</a>
			</td>
			<td align="center">
				<textarea data-bind="{title}" class="metainfo metatitle" data-titleidentifier="<?php echo $k + 1;?>"><?php echo isset($row->metainfos->meta_title) ? $row->metainfos->meta_title : null;?></textarea>
			</td>
			<td align="center">
				<textarea data-bind="{desc}" class="metainfo metadesc" data-descidentifier="<?php echo $k + 1;?>"><?php echo isset($row->metainfos->meta_desc) ? $row->metainfos->meta_desc : null;?></textarea>
			</td>
			<td align="center">
				<select class="robots_directive" id="jmap_metainfo_robots_<?php echo $k + 1;?>" data-robotsidentifier="<?php echo $k + 1;?>">
					<option value="">
						<?php echo JText::_('COM_JMAP_USE_GLOBAL');?>
					</option>
					<option value="index, follow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'index, follow' ? 'selected="selected"' : null;?>>
						<?php echo JText::_('COM_JMAP_USE_INDEX_FOLLOW');?>
					</option>
					<option value="noindex, follow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'noindex, follow' ? 'selected="selected"' : null;?>>
						<?php echo JText::_('COM_JMAP_USE_NOINDEX_FOLLOW');?>
					</option>
					<option value="index, nofollow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'index, nofollow' ? 'selected="selected"' : null;?>>
						<?php echo JText::_('COM_JMAP_USE_INDEX_NOFOLLOW');?>
					</option>
					<option value="noindex, nofollow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'noindex, nofollow' ? 'selected="selected"' : null;?>>
						<?php echo JText::_('COM_JMAP_USE_NOINDEX_NOFOLLOW');?>
					</option>
				</select>
			</td>
			<td class="metaimage" align="center">
				<?php
					$this->mediaField->value = null;
					if(isset($row->metainfos)) {
						$this->mediaField->value = $row->metainfos->meta_image;
					}
					$this->mediaField->id = 'jform_media_identifier_' . ($k + 1);
					$this->mediaField->name = 'jform_media_identifier_' . ($k + 1);
					$this->mediaField->dataIdentifier = ($k + 1);
					if(method_exists($this->mediaField, 'renderField')) {
						echo $this->mediaField->renderField(); // Joomla 3.3+
					} elseif(method_exists($this->mediaField, 'getControlGroup')) {
						echo $this->mediaField->getControlGroup(); // Joomla 3.2
					} else {
						echo $this->mediaField->input; // Joomla 3.1
					}
				?>
			</td>
			<td align="center">
				<button class="btn btn-primary" data-action="savemeta" data-save="<?php echo $k + 1;?>"><span class="glyphicon glyphicon-floppy-disk"></span> <?php echo JText::_('COM_JMAP_METAINFO_SAVE');?></button>
				<button class="btn btn-danger" data-action="deletemeta" data-delete="<?php echo $k + 1;?>"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo JText::_('COM_JMAP_METAINFO_DELETE');?></button>
			</td>
			<td align="center">
				<fieldset class="radio btn-group" data-action="statemeta" data-state="<?php echo $k + 1;?>">
					<?php 
						$published = isset($row->metainfos->published) ? $row->metainfos->published : 1;
						echo JHTML::_ ( 'select.booleanlist', 'published' . $k, null, $published);
					?>
				</fieldset>
			</td>
			<td class="hidden-phone hidden-tablet" align="center">
				<fieldset class="radio btn-group" data-action="excludedmeta" data-state="<?php echo $k + 1;?>">
					<?php 
						$excluded = isset($row->metainfos->excluded) ? $row->metainfos->excluded : 0;
						echo JHTML::_ ( 'select.booleanlist', 'excluded' . $k, null, $excluded);
					?>
				</fieldset>
			</td>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( JText::_('COM_JMAP_METAINFO_NOLINKS_ONTHISPAGE') );
	}
	?>
	</tbody>
	</table>
</form>