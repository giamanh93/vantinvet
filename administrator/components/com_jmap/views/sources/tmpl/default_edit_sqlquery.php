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
<!-- ************************** QUERY STRING LINK PARAMS ************************** -->
<div id="accordion_datasource_sqlquery_querystring" class="sqlquerier panel panel-info panel-group">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_sqlquery_querystring">
		<h4>
			<?php echo JText::_('COM_JMAP_SQLQUERY_STRINGLINK_PARAMS' ); ?> 
			<a href="http://storejextensions.org/jsitemap_professional_documentation_datasources.html" class="hasPopover glyphicon glyphicon-info-sign dialog_trigger" data-content="<?php echo JText::_('COM_JMAP_HELP_EXPLAIN');?>"></a> 
		</h4>
	</div>
	<div class="panel-body  panel-collapse collapse" id="datasource_sqlquery_querystring">
		<table class="admintable">
			<tr>
				<td class="key left_title">
					<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_VIEWNAME_DESC' ); ?>">
						<?php echo JText::_('COM_JMAP_SQLQUERY_VIEWNAME' ); ?>
					</label>
				</td>
				<td class="right_details">
					<input type="text" name="params[view]" value="<?php echo $this->record->params->get('view', '');?>" />
				</td>
			</tr> 
			
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_ENTER_ADDITIONAL_QUERYSTRING_PARAMS_DESC');?>"><?php echo JText::_('COM_JMAP_ENTER_ADDITIONAL_QUERYSTRING_PARAMS');?></label></span>
				</td>
				<td class="paramlist_value">
					<input size="100" type="text" name="params[additionalquerystring]" id="paramsadditionalquerystring" value="<?php echo $this->record->params->get('additionalquerystring', '');?>" class="text_area">
				</td>
			</tr>
			
			<?php if($this->hasRouteManifest): ?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip">
					<label id="paramsguess_sef_itemid-lbl" for="paramsguess_sef_itemid" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_GUESS_SEF_ITEMID_DESC');?>"><?php echo JText::_('COM_JMAP_GUESS_SEF_ITEMID');?></label></span>
				</td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_paramsguess_sef_itemid" class="radio btn-group">
						<?php echo JHTML::_('select.booleanlist', 'params[guess_sef_itemid]', null,  $this->record->params->get('guess_sef_itemid', 0), 'JYES', 'JNO', 'params_guess_sefitemid_');?>
					</fieldset>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SEF_ITEMID_DESC');?>"><?php echo JText::_('COM_JMAP_SEF_ITEMID');?></label></span>
				</td>
				<td class="paramlist_value">
					<?php echo $this->lists['sef_itemid']; ?>
				</td>
			</tr>
		</table>
  	</div>
</div>
		
<div id="accordion_datasource_sqlquery" class="sqlquerier panel panel-info panel-group adminform">
	<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_sqlquery">
		<h4>
			<?php echo JText::_('COM_JMAP_SQLQUERY_INFO' ); ?> 
		 	<a href="http://storejextensions.org/jsitemap_professional_documentation_datasources.html" class="hasPopover glyphicon glyphicon-info-sign dialog_trigger" data-content="<?php echo JText::_('COM_JMAP_HELP_EXPLAIN');?>"></a>
		</h4>
	</div>
	<div class="panel-body panel-collapse collapse" id="datasource_sqlquery">
	
		<!-- ************************** MAIN TABLE ************************** -->
		<div id="accordion_datasource_sqlquery_maintable" class="panel panel-warning panel-group">
			<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_sqlquery_maintable"><h4><?php echo JText::_('COM_JMAP_SQLQUERY_MAINTABLE' ); ?></h4></div>
			<div class="panel-body panel-collapse collapse" id="datasource_sqlquery_maintable">
				<table class="admintable">
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_COMPONENTNAME_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_COMPONENTNAME' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists['components']; ?>
						</td>
					</tr> 
						
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_TABLENAME_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_TABLENAME' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists['tablesMaintable']; ?>
						</td>
					</tr> 
						
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_TITLEFIELD_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_TITLEFIELD' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['fieldsTitle']; ?> 
							<label class="as label label-primary">AS</label>
							<input type="text" name="sqlquery_managed[titlefield_as]" value="<?php echo $this->registrySqlQueryManaged->get('titlefield_as', '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_IDFIELD_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_IDFIELD' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['fieldsID']; ?> 
							<label class="as label label-primary">AS</label>
							<input type="text" name="sqlquery_managed[idfield_as]" value="<?php echo $this->registrySqlQueryManaged->get('idfield_as', '');?>" />
							<label class="as label label-primary"><?php echo JText::_('COM_JMAP_SQLQUERY_USEALIAS');?></label>
							<fieldset id="jform_datasource_usealias_id" class="radio btn-group">
								<?php echo JHTML::_('select.booleanlist', 'sqlquery_managed[use_alias]', null, $this->registrySqlQueryManaged->get('use_alias', 0), 'JYES', 'JNO', 'params_usealias_id_');?>
							</fieldset>
							<label class="as label label-primary filter"><?php echo JText::_('COM_JMAP_URL_FILTER');?></label>
							<fieldset id="jform_datasource_usealias_id" class="radio btn-group">
								<?php echo JHTML::_('select.booleanlist', 'sqlquery_managed[url_filter_id]', null, $this->registrySqlQueryManaged->get('url_filter_id', 0), 'JYES', 'JNO', 'params_url_filter_id_');?>
							</fieldset>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_CATIDNAME_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_CATIDNAME' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['fieldsCatid']; ?> 
							<label class="as label label-primary">AS</label>
							<input type="text" name="sqlquery_managed[catidfield_as]" value="<?php echo $this->registrySqlQueryManaged->get('catidfield_as', '');?>" />
							<label class="as label label-primary"><?php echo JText::_('COM_JMAP_SQLQUERY_USEALIAS');?></label>
							<fieldset id="jform_datasource_usealias_catid" class="radio btn-group">
								<?php echo JHTML::_('select.booleanlist', 'sqlquery_managed[use_catalias]', null, $this->registrySqlQueryManaged->get('use_catalias', 0), 'JYES', 'JNO', 'params_usealias_catid_');?>
							</fieldset>
							<label class="as label label-primary filter"><?php echo JText::_('COM_JMAP_URL_FILTER');?></label>
							<fieldset id="jform_datasource_usealias_catid" class="radio btn-group">
								<?php echo JHTML::_('select.booleanlist', 'sqlquery_managed[url_filter_catid]', null, $this->registrySqlQueryManaged->get('url_filter_catid', 0), 'JYES', 'JNO', 'params_url_filter_catid_');?>
							</fieldset>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ADDITIONAL_PARAMS_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_ADDITIONAL_PARAMS' ); ?>
							</label>
						</td>
						<td class="right_details">
							<textarea type="text" name="sqlquery_managed[additionalparams_maintable]" id="sqlquery_rawparams" rows="5" cols="40" ><?php echo $this->registrySqlQueryManaged->get("additionalparams_maintable", '');?></textarea>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 1); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where1Maintable']; ?> 
							<?php echo $this->lists ['where1MaintableOperators']; ?> 
							<input type="text" name="sqlquery_managed[where1_value_maintable]" value="<?php echo $this->registrySqlQueryManaged->get('where1_value_maintable', '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 2); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where2Maintable']; ?> 
							<?php echo $this->lists ['where2MaintableOperators']; ?> 
							<input type="text" name="sqlquery_managed[where2_value_maintable]" value="<?php echo $this->registrySqlQueryManaged->get('where2_value_maintable', '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where3Maintable']; ?> 
							<?php echo $this->lists ['where3MaintableOperators']; ?> 
							<input type="text" name="sqlquery_managed[where3_value_maintable]" value="<?php echo $this->registrySqlQueryManaged->get('where3_value_maintable', '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ORDERBY_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_ORDERBY' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['orderByMaintable']; ?> 
							<label class="order hasPopover label label-primary" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ORDERBY_DESC2' ); ?>"><span class="glyphicon glyphicon-sort"></span></label>
							<?php echo $this->lists ['orderByDirectionMaintable']; ?> 
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_GROUPBY_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_GROUPBY' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['groupByMaintable']; ?> 
						</td>
					</tr> 
				</table>
			</div>
		</div>		
		
		<?php for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++):?>
		<!-- **************************  JOIN TABLE #n **************************  -->
		<div id="accordion_datasource_sqlquery_jointable<?php echo $jt;?>" class="panel panel-warning panel-group">
			<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_sqlquery_jointable<?php echo $jt;?>"><h4><?php echo JText::_('COM_JMAP_SQLQUERY_JOINTABLE'. $jt ); ?></h4></div>
			<div class="panel-body panel-collapse collapse" id="datasource_sqlquery_jointable<?php echo $jt;?>">
				<span data-role="jointable_resetter" class="hasPopover glyphicon glyphicon-remove-circle jointable_resetter" data-placement="left" data-content="<?php echo JText::_('COM_JMAP_RESET_JOINTABLE_SETTINGS');?>"></span> 
				<table class="admintable">
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_SELECTJOINTABLES_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_SELECTJOINTABLES' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists['tablesJoinFromJointable'.$jt]; ?>
							<?php echo $this->lists ['jointypeJointable'.$jt]; ?> 
							<?php echo $this->lists['tablesJoinWithJointable'.$jt]; ?>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_SELECTONFIELDS_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_SELECTONFIELDS' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['fieldsJoinFromJointable'.$jt]; ?> 
							<label class="as label label-primary marked largealignment">=</label>
							<?php echo $this->lists ['fieldsJoinWithJointable'.$jt]; ?> 
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_GENERIC_SELECT_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_GENERIC_SELECT' ); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['fieldsSelectJointable'.$jt]; ?> 
							<label class="as label label-primary">AS</label>
							<input type="text" name="sqlquery_managed[field_as_jointable<?php echo $jt;?>]" value="<?php echo $this->registrySqlQueryManaged->get('field_as_jointable'.$jt, '');?>" />
							<label class="as label label-primary"><?php echo JText::_('COM_JMAP_SQLQUERY_USECATEGORYTITLE');?></label>
							<fieldset id="jform_datasource_use_category_title_jointable<?php echo $jt;?>" class="radio btn-group">
								<?php echo JHTML::_('select.booleanlist', 'sqlquery_managed[use_category_title_jointable' . $jt . ']', null, $this->registrySqlQueryManaged->get('use_category_title_jointable'.$jt, 0), 'JYES', 'JNO', "params_use_category_title_jointable$jt");?>
							</span>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ADDITIONAL_PARAMS_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_ADDITIONAL_PARAMS' ); ?>
							</label>
						</td>
						<td class="right_details">
							<textarea type="text" name="sqlquery_managed[additionalparams_jointable<?php echo $jt;?>]" id="sqlquery_rawparams" rows="5" cols="40" ><?php echo $this->registrySqlQueryManaged->get('additionalparams_jointable'.$jt, '');?></textarea>
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 1); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where1Jointable'.$jt]; ?> 
							<?php echo $this->lists ['where1Jointable'.$jt.'Operators']; ?> 
							<input type="text" name="sqlquery_managed[where1_value_jointable<?php echo $jt;?>]" value="<?php echo $this->registrySqlQueryManaged->get('where1_value_jointable'.$jt, '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 2); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where2Jointable'.$jt]; ?> 
							<?php echo $this->lists ['where2Jointable'.$jt.'Operators']; ?> 
							<input type="text" name="sqlquery_managed[where2_value_jointable<?php echo $jt;?>]" value="<?php echo $this->registrySqlQueryManaged->get('where2_value_jointable'.$jt, '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_WHEREEXTRA' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['where3Jointable'.$jt]; ?> 
							<?php echo $this->lists ['where3Jointable'.$jt.'Operators']; ?> 
							<input type="text" name="sqlquery_managed[where3_value_jointable<?php echo $jt;?>]" value="<?php echo $this->registrySqlQueryManaged->get('where3_value_jointable'.$jt, '');?>" />
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ORDERBY_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_ORDERBY' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['orderByJointable'.$jt]; ?> 
							<label class="order hasPopover label label-primary" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_ORDERBY_DESC2' ); ?>"><span class="glyphicon glyphicon-sort"></span></label>
							<?php echo $this->lists ['orderByDirectionJointable'.$jt]; ?> 
						</td>
					</tr> 
					
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_GROUPBY_DESC' ); ?>">
								<?php echo sprintf(JText::_('COM_JMAP_SQLQUERY_GROUPBY' ), 3); ?>
							</label>
						</td>
						<td class="right_details">
							<?php echo $this->lists ['groupByJointable'.$jt]; ?> 
						</td>
					</tr> 
				</table>
			</div>
		</div>		
		<?php endfor;?>
		
		<!-- ************************** AUTO GENERATED SQL QUERY ************************** -->
		<?php if($this->record->id):?>
		<div id="accordion_datasource_sqlquery_autogenerated" class="panel panel-warning panel-group">
			<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#datasource_sqlquery_autogenerated"><h4><?php echo JText::_('COM_JMAP_SQLQUERY_AUTOGENERATED_SQL' ); ?></h4></div>
			<div class="panel-body panel-collapse collapse" id="datasource_sqlquery_autogenerated">
				<table class="admintable">
					<tr>
						<td class="key left_title">
							<label for="content" class="hasPopover" data-content="<?php echo JText::_('COM_JMAP_SQLQUERY_RAW_DESC' ); ?>">
								<?php echo JText::_('COM_JMAP_SQLQUERY_RAW' ); ?>
							</label>
						</td>
						<td class="right_details">
							<textarea type="text" name="sqlquery" id="sqlquery"><?php echo $this->record->sqlquery;?></textarea>
							<button id="regenerate_button" class="btn btn-danger hasPopover" data-content="<?php echo JText::_('COM_JMAP_REGENERATE_QUERY_DESC' ); ?>"> 
								<span class="glyphicon glyphicon-refresh"></span> <?php echo JText::_('COM_JMAP_REGENERATE_QUERY' ); ?>
							</button>
						</td>
					</tr> 
				</table>
			</div>
		</div>
		<?php endif;?>
	</div>
</div>