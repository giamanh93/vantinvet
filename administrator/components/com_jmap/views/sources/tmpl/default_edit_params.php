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
?>
<div id="accordion_datasource_parameters" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_parameters"><h4><?php echo JText::_('COM_JMAP_Parameters' ); ?></h4></div>
	<div class="panel-body panel-collapse collapse" id="datasource_parameters">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsopentarget-lbl" for="paramsopentarget" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_OPEN_TARGET_DESC');?>"><?php echo JText::_('COM_JMAP_OPEN_TARGET');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_opentarget" class="radio btn-group">
						<?php 
							$arr = array(
								JHTML::_('select.option',  '', JText::_('JGLOBAL_USE_GLOBAL' ) ),
								JHTML::_('select.option',  '_self', JText::_('COM_JMAP_SELF_WINDOW' ) ),
								JHTML::_('select.option',  '_blank', JText::_('COM_JMAP_BLANK_WINDOW' ) ),
								JHTML::_('select.option',  '_parent', JText::_('COM_JMAP_PARENT_WINDOW' ) )
							);
							echo JHTML::_('select.radiolist',  $arr, 'params[opentarget]', '', 'value', 'text', $this->record->params->get('opentarget', ''), 'params_opentarget_');
						?>
					</fieldset>
				</td>
			</tr>
			<?php if($this->record->type != 'links'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdisable_acl-lbl" for="paramsdisable_acl" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_DISABLE_ACL_DESC');?>"><?php echo JText::_('COM_JMAP_DISABLE_ACL');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_disable_acl" class="radio btn-group">
						<?php 
							$arr = array(
								JHTML::_('select.option',  '', JText::_('JGLOBAL_USE_GLOBAL' ) ),
								JHTML::_('select.option',  'enabled', JText::_('JENABLED' ) ),
								JHTML::_('select.option',  'disabled', JText::_('JDISABLED' ) )
							);
							echo JHTML::_('select.radiolist',  $arr, 'params[disable_acl]', '', 'value', 'text', $this->record->params->get('disable_acl', ''), 'params_disable_acl');
						?>
					</fieldset>
				</td>
			</tr>
			<?php endif; ?>
			<?php if(($this->record->type == 'links' || $this->record->type == 'user') && array_key_exists('languages', $this->lists)):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslanguages-lbl" for="paramslanguages" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_DATASOURCE_LANGUAGE_DESC');?>"><?php echo JText::_('COM_JMAP_DATASOURCE_LANGUAGE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_languages" class="radio btn-group">
						<?php echo $this->lists['languages'];?>
					</fieldset>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramshtmlinclude-lbl" for="paramshtmlinclude" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_HTML_ELEMENTS_INCLUDE_DESC');?>"><?php echo JText::_('COM_JMAP_HTML_ELEMENTS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_htmlinclude" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[htmlinclude]', null,  $this->record->params->get('htmlinclude', 1), 'JYES', 'JNO', 'params_htmlinclude_');?>
					</fieldset>
				</td>
			</tr>
			<!-- RSS feed include if supported extension --> 
			<?php if($this->supportedRSSExtension || $this->record->type == 'plugin'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsrssinclude-lbl" for="paramsrssinclude" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_RSS_ELEMENTS_INCLUDE_DESC');?>"><?php echo JText::_('COM_JMAP_RSS_ELEMENTS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_rssinclude" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[rssinclude]', null,  $this->record->params->get('rssinclude', 1), 'JYES', 'JNO', 'params_rssinclude_');?>
					</fieldset>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SHOWED_SOURCE_TITLE_DESC');?>"><?php echo JText::_('COM_JMAP_SHOWED_SOURCE_TITLE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[title]" id="paramstitle" value="<?php echo htmlspecialchars($this->record->params->get('title', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsshow_title-lbl" for="paramsshow_title" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SHOW_SOURCE_TITLE_DESC');?>"><?php echo JText::_('COM_JMAP_SHOW_SOURCE_TITLE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_showtitle" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[showtitle]', null,  $this->record->params->get('showtitle', 1), 'JYES', 'JNO', 'params_showtitle_');?>
					</fieldset>
				</td>
			</tr>
			<!-- User Data source --> 
			<?php if($this->hasCreatedDate):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramscreated_date-lbl" for="paramscreated_date" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_CREATED_DATE_DESC');?>"><?php echo JText::_('COM_JMAP_CREATED_DATE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_created_date" class="radio btn-group">
						<?php 
							$monthsOptions = array();
							$monthsOptions[] = JHTML::_('select.option',  '', JText::_('COM_JMAP_NO_DATE_LIMITS' ));
							for($months=1,$maxmonths=12;$months<=$maxmonths;$months++) {
								$monthsOptions[] = JHTML::_('select.option',  $months, JText::_('COM_JMAP_LAST_' . $months . 'MONTH' ));
							}
							echo JHTML::_('select.genericlist',  $monthsOptions, 'params[created_date]', '', 'value', 'text', $this->record->params->get('created_date', ''), 'params_created_date');
						?>
					</fieldset>
				</td>
			</tr>
			<?php endif;?>
			<?php if($this->hasManifest):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmultilevel_tree-lbl" for="paramsmultilevel_tree" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_MULTILEVEL_CATEGORIES_DESC');?>"><?php echo JText::_('COM_JMAP_MULTILEVEL_CATEGORIES');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_multilevel_categories" class="radio btn-group">
						<?php 
							$arr = array(
								JHTML::_('select.option',  '', JText::_('JGLOBAL_USE_GLOBAL' ) ),
								JHTML::_('select.option',  1, JText::_('JENABLED' ) ),
								JHTML::_('select.option',  0, JText::_('JDISABLED' ) )
							);
							echo JHTML::_('select.radiolist',  $arr, 'params[multilevel_categories]', '', 'value', 'text', $this->record->params->get('multilevel_categories', ''), 'params_multilevel_categories_');
						?>
					</fieldset>
				</td>
			</tr>
			<?php endif;?>
			<?php if($this->record->type == 'content'):?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_orderbydate-lbl" for="params_orderbydate" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_ORDERBYDATE_DESC');?>"><?php echo JText::_('COM_JMAP_ORDERBYDATE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_orderbydate" class="radio btn-group">
							<?php 
							$arr = array(
									JHTML::_('select.option',  0, JText::_('JNO' ) ),
									JHTML::_('select.option',  1, JText::_('COM_JMAP_YES_BYDATE_CREATED' ) ),
									JHTML::_('select.option',  2, JText::_('COM_JMAP_YES_BYDATE_MODIFIED' ) )
							);
							echo JHTML::_('select.radiolist',  $arr, 'params[orderbydate]', '', 'value', 'text', $this->record->params->get('orderbydate', 0), 'params_orderbydate_');?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_orderbyalpha-lbl" for="params_orderbyalpha" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_ORDERBYALPHA_DESC');?>"><?php echo JText::_('COM_JMAP_ORDERBYALPHA');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_orderbyalpha" class="radio btn-group">
							<?php echo JHTML::_('select.booleanlist', 'params[orderbyalpha]', null,  $this->record->params->get('orderbyalpha', 0), 'JYES', 'JNO', 'params_orderbyalpha_');?>
						</fieldset>
					</td>
				</tr>
				<!-- Rule expand state by data source level --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_show_content_expanded-lbl" for="params_show_content_expanded" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC');?>"><?php echo JText::_('COM_JMAP_SHOW_CONTENT_EXPANDED');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_show_content_expanded" class="radio btn-group">
							<?php 
								$arr = array(
									JHTML::_('select.option',  '', JText::_('JGLOBAL_USE_GLOBAL' ) ),
									JHTML::_('select.option',  2, JText::_('JENABLED' ) ),
									JHTML::_('select.option',  1, JText::_('JDISABLED' ) )
								);
								echo JHTML::_('select.radiolist',  $arr, 'params[show_content_expanded]', '', 'value', 'text', $this->record->params->get('show_content_expanded', ''), 'show_content_expanded_');
							?>
						</fieldset>
					</td>
				</tr>
			<?php endif; ?>
			<?php if($this->record->type == 'content' || $this->record->type == 'plugin'):?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslinkablecontentcats-lbl" for="paramslinkablecontentcats" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_LINKABLE_CONTENT_CATS_DESC');?>"><?php echo JText::_('COM_JMAP_LINKABLE_CONTENT_CATS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_linkable_cats" class="radio btn-group">
							<?php echo JHTML::_('select.booleanlist', 'params[linkable_content_cats]', null,  $this->record->params->get('linkable_content_cats', 0), 'JYES', 'JNO', 'params_linkable_content_cats_');?>
						</fieldset>
					</td>
				</tr>
			<?php endif; ?>
			<?php if($this->record->type == 'content' || ($this->record->type == 'user' && $this->isCategorySource && $this->record->params->get('view', null))): ?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmergemenutree-lbl" for="paramsmergemenutree" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_MERGE_MENU_TREE_DESC');?>"><?php echo JText::_('COM_JMAP_MERGE_MENU_TREE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_merge_menu_tree" class="radio btn-group">
							<?php 
								$arr = array(
									JHTML::_('select.option', '', JText::_('JNO' ) ),
									JHTML::_('select.option', 'yes', JText::_('JYES' ) ),
									JHTML::_('select.option', 'yeshide', JText::_('COM_JMAP_YES_HIDE' ) )
								);
								echo JHTML::_('select.radiolist',  $arr, 'params[merge_menu_tree]', '', 'value', 'text', $this->record->params->get('merge_menu_tree', ''), 'merge_menu_tree_');
							?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmergemenutreelevels-lbl" for="paramsmergemenutreelevels" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_MERGE_MENU_TREE_LEVELS_DESC');?>"><?php echo JText::_('COM_JMAP_MERGE_MENU_TREE_LEVELS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_merge_menu_tree_levels" class="radio btn-group">
							<?php 
								$arr = array(
									JHTML::_('select.option', 'toplevel', JText::_('COM_JMAP_MERGE_TOPLEVEL' ) ),
									JHTML::_('select.option', 'childlevels', JText::_('COM_JMAP_MERGE_CHILDLEVEL' ) ),
								);
								echo JHTML::_('select.radiolist',  $arr, 'params[merge_menu_tree_levels]', '', 'value', 'text', $this->record->params->get('merge_menu_tree_levels', 'toplevel'), 'merge_menu_tree_levels_');
							?>
						</fieldset>
					</td>
				</tr>
			<?php endif;?>
			<?php if($this->record->type == 'user'):?>
				<?php if($this->hasItemsCategorization): ?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslinkablecats-lbl" for="paramslinkablecats" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_LINKABLE_CATS_DESC');?>"><?php echo JText::_('COM_JMAP_LINKABLE_CATS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_linkable_cats" class="radio btn-group">
							<?php 
								$arr = array(
									JHTML::_('select.option', '', JText::_('JNO' ) ),
									JHTML::_('select.option', 'yes', JText::_('JYES' ) ),
									JHTML::_('select.option', 'yeshide', JText::_('COM_JMAP_YES_HIDE' ) )
								);
								echo JHTML::_('select.radiolist',  $arr, 'params[linkable_cats]', '', 'value', 'text', $this->record->params->get('linkable_cats', ''), 'params_linkable_cats_');
							?>
						</fieldset>
					</td>
				</tr>
				<?php endif;?>
				<!-- Rule expand state by data source level --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_show_content_expanded-lbl" for="params_show_content_expanded" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC');?>"><?php echo JText::_('COM_JMAP_SHOW_CONTENT_EXPANDED');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_show_content_expanded" class="radio btn-group">
							<?php 
								$arr = array(
									JHTML::_('select.option',  '', JText::_('JGLOBAL_USE_GLOBAL' ) ),
									JHTML::_('select.option',  2, JText::_('JENABLED' ) ),
									JHTML::_('select.option',  1, JText::_('JDISABLED' ) )
								);
								echo JHTML::_('select.radiolist',  $arr, 'params[show_content_expanded]', '', 'value', 'text', $this->record->params->get('show_content_expanded', ''), 'show_content_expanded_');
							?>
						</fieldset>
					</td>
				</tr>
				<!-- Parameters section to perform replacements in the final SEF rewritten links --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_enable_sef_links_replacements-lbl" for="params_enable_sef_links_replacements" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_ENABLE_SEF_LINKS_REPLACEMENTS_DESC');?>"><?php echo JText::_('COM_JMAP_ENABLE_SEF_LINKS_REPLACEMENTS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_enable_sef_links_replacements" class="radio btn-group">
							<?php echo JHTML::_('select.booleanlist', 'params[enable_sef_links_replacements]', null,  $this->record->params->get('enable_sef_links_replacements', 0), 'JYES', 'JNO', 'params_enable_sef_links_replacements_');?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title">
						<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_SOURCE_DESC');?>"><?php echo JText::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_SOURCE');?></label></span>
					</td>
					<td class="paramlist_value">
						<input type="text" name="params[sef_links_replacements_source]" id="paramstitle" value="<?php echo htmlspecialchars($this->record->params->get('sef_links_replacements_source', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title">
						<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_TARGET_DESC');?>"><?php echo JText::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_TARGET');?></label></span>
					</td>
					<td class="paramlist_value">
						<input type="text" name="params[sef_links_replacements_target]" id="paramstitle" value="<?php echo htmlspecialchars($this->record->params->get('sef_links_replacements_target', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
					</td>
				</tr>
				<!-- Debug SQL data source --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdebug_mode-lbl" for="paramsdebug_mode" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_DEBUG_MODE_DESC');?>"><?php echo JText::_('COM_JMAP_DEBUG_MODE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_debugmode" class="radio btn-group">
							<?php echo JHTML::_('select.booleanlist', 'params[debug_mode]', null,  $this->record->params->get('debug_mode', 0), 'JYES', 'JNO', 'params_debugmode_');?>
						</fieldset>
					</td>
				</tr>
			<?php endif;?>
			<!-- Menu Data source --> 
			<?php if($this->record->type == 'menu'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdounpublished-lbl" for="paramsdounpublished" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_DOUPUBLISHED_DESC');?>"><?php echo JText::_('COM_JMAP_DOUPUBLISHED');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_dounpublished" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[dounpublished]', null,  $this->record->params->get('dounpublished', 0), 'JYES', 'JNO', 'params_dounpublished_');?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsinclude_external_links-lbl" for="paramsinclude_external_links" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_INCLUDE_EXTERNAL_LINKS_DESC');?>"><?php echo JText::_('COM_JMAP_INCLUDE_EXTERNAL_LINKS');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_include_external_links" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[include_external_links]', null,  $this->record->params->get('include_external_links', 1), 'JYES', 'JNO', 'params_include_external_links_');?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label for="paramsmaxlevels" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_MAXLEVELS_DESC');?>"><?php echo JText::_('COM_JMAP_MAXLEVELS');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_maxlevels" class="radio btn-group">
						<?php echo JHTML::_('select.integerlist', 1, 100, 1, 'params[maxlevels]', null,  $this->record->params->get('maxlevels', 5));?>
					</fieldset>
				</td>
			</tr>
			<?php endif;?>
		</table>
		<input type="hidden" name="params[datasource_extension]" value="<?php echo $this->record->params->get('datasource_extension', '');?>"/>
	</div>
</div> 