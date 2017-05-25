/**
* Data source SQL query managed JS client
* 
* @package JMAP::SOURCES::administrator::components::com_jmap 
* @subpackage js 
* @author Joomla! Extensions Store
* @copyright (C)2015 Joomla! Extensions Store
* @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
jQuery(function($){
	var Sources = $.newClass ({
		/**
		 * Main selector
		 * @access public
		 * @property prototype
		 * @var array
		 */
		selector : null,
	 
		/**
		 * Object initializer
		 * 
		 * @access public
		 * @param string selector 
		 */
		init : function(selector) {
			/**
			 * Init prototype properties (set method)
			 * @property prototype
			 */
			this.constructor.prototype.selector = selector;
			
			// Register app interactions
			this.registerEvents();
		},
	
		/**
		 * Register for user interaction 
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		registerEvents : function() {
			var bind = this;
			
			// Register events main selector for tables dropdown 
			$(this.selector).on('change', {bind:this}, function(event) {
				event.data.bind.getAjaxContent(event.target); 
			});
			
			// Register events for menu priorities buttons and dropdowns
			$('button[data-role=priority_action]').on('click', {bind:this}, function(event) {
				event.preventDefault();
				var buttonAction = $(event.target).data('action');
				var buttonType = $(event.target).data('type');
				if(buttonAction && buttonType) {
					event.data.bind.manageitemAjaxPriority(buttonAction, buttonType);
				}
			});
			
			$('#paramsmenu_priorities, #paramscats_priorities').on('change', {bind:this}, function(event){
				var buttonType = $(event.target).data('type');
				event.data.bind.getitemAjaxPriority(buttonType);
			});
			
			$('select, input, #sqlquery_rawparams', '#accordion_datasource_sqlquery').on('change', function(){
				$('#regenerate_query').val(1);
			});
			
			$('label.radio.btn', '#accordion_datasource_sqlquery').on('click', function(){
				$('#regenerate_query').val(1);
			});
			
			$('#params_created_date').on('change', function(){
				$('#regenerate_query').val(1);
			});
			
			$('#regenerate_button').on('click', function(){
				$('#regenerate_query').val(1);
				Joomla.submitbutton('sources.applyEntity');
			});
			
			$('.dialog_trigger').on('click', function(event){
				event.stopPropagation();
				$('#suggestions_modal').modal('show');
			});
			
			$('span[data-role=jointable_resetter]').on('click', function(event){
				// Namespace next table
				var nextTable = $(this).nextAll('table');
				$('select', nextTable).each(function(index, elem){
					$(elem).val('');
				});
				
				$('input', nextTable).each(function(index, elem){
					$(elem).val('');
				});
			});
			
			// Intercept modal bootstrap autofocus
			$('#suggestions_modal').on('shown.bs.modal', function() {
			    $(document).off('focusin.modal');
			    $('iframe', this).attr('src', 'index.php?option=com_jmap&task=help.display&tmpl=component&partial=true');
			});
			$('#suggestions_modal').draggable({ 
				iframeFix: true, 
				handle: 'div.modal-header', 
				start: function(){
					var iframe = $(this).find("iframe");
	                if(iframe.length > 0){
	                	$(iframe).before('<div id="iframe_overlay" style=" top: 0;bottom: 0;right: 0;left: 0;position: absolute;"></div>');
	                }
	            },
	            stop: function(){
	            	$('#iframe_overlay').remove();
	            }
			});
			
			// Always switch on the multilevel if the menu merge is required
			$('#jform_datasource_merge_menu_tree label').on('click', function(jqEvent){
				if(jqEvent.target.nodeName.toLowerCase() == 'label') {
					var labelId = $(this).attr('id');
					if(labelId.match(/yes/i)) {
						$('#params_multilevel_categories_1-lbl').trigger('click');
					}
				}
			});
			
			// Change the label accordingly based on the exclude/include switcher
			$('input[name=params\\[choose_exclusion_way\\]]').on('change', function(jqEvent){
				var selectedExclusionWay = $(this).prop('value');
				
				switch (selectedExclusionWay) {
					case 'exclude':
						$('div[data-target=#datasource_excludecats] h4').text(COM_JMAP_CATEGORIES_EXCLUSION);
						$('div[data-target=#datasource_excludearticles] h4').text(COM_JMAP_ARTICLES_EXCLUSION);
						$('#paramschoose_catexclusion-lbl').text(COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION);
						$('#paramschoose_catexclusion-lbl').data('popover').options.content = COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION_DESC;
						$('#paramschoose_artexclusion-lbl').text(COM_JMAP_CHOOSE_ARTICLES_EXCLUSION);
						$('#paramschoose_artexclusion-lbl').data('popover').options.content = COM_JMAP_CHOOSE_ARTICLES_EXCLUSION_DESC;
					break;
					
					case 'include':
						$('div[data-target=#datasource_excludecats] h4').text(COM_JMAP_CATEGORIES_INCLUSION);
						$('div[data-target=#datasource_excludearticles] h4').text(COM_JMAP_ARTICLES_INCLUSION);
						$('#paramschoose_catexclusion-lbl').text(COM_JMAP_CHOOSE_CATEGORIES_INCLUSION);
						$('#paramschoose_catexclusion-lbl').data('popover').options.content = COM_JMAP_CHOOSE_CATEGORIES_INCLUSION_DESC;
						$('#paramschoose_artexclusion-lbl').text(COM_JMAP_CHOOSE_ARTICLES_INCLUSION);
						$('#paramschoose_artexclusion-lbl').data('popover').options.content = COM_JMAP_CHOOSE_ARTICLES_INCLUSION_DESC;
					break;
				}
			});
			
			// Register events for links data source type adder/deleter rows
			$('button[data-role=rawlinks_action]').on('click', {bind:this}, function(event) {
				event.preventDefault();
				var buttonAction = $(event.target).data('action');
				// Add a row
				if(buttonAction == 'add') {
					var numberOfRows = $('table.rawlinks_table_links tr').length;
					var rowSnippet = '<tr>' +
											'<td class="key left_title">' +
											'<label class="title_label">' + COM_JMAP_RAW_SOURCE_LINK.replace('%d', (numberOfRows + 1)) + '</label>' +
											'<input type="checkbox" value=""/> ' +
										'</td>' +
										'<td class="right_details">' +
											'<label class="as label label-primary">Title</label> ' +
											'<input class="sitemap_rawtitle" type="text" name="sqlquery_managed[title][]" value=""> ' +
											'<label class="as label label-primary">Link</label> ' +
											'<input class="sitemap_rawlink" type="text" data-validation="required url" name="sqlquery_managed[link][]" value=""> ' +
										'</td>' +
									'</tr>';
					$('table.rawlinks_table_links').append(rowSnippet);
				}
				
				// Delete selected rows
				if(buttonAction == 'delete') {
					// Get selected records
					var selectedRecords = $('table.rawlinks_table_links input[type=checkbox]:checked');
					if(!selectedRecords.length) {
						alert(COM_JMAP_SELECTED_LINK_RECORDS);
						return false;
					}
					// Remove selected records
					$('table.rawlinks_table_links input[type=checkbox]:checked').each(function(index, element){
						$(element).parents('tr').remove();
					});
					
					// Array reordering of rows
					$('table.rawlinks_table_links tr').each(function(index, tableRow){
						$('label.title_label', tableRow).text(COM_JMAP_RAW_SOURCE_LINK.replace('%d', (index + 1)));
					});
				}
			});
			
			// Register events for select all links
			$('input[type=checkbox][data-role=selectall]').on('click', function(jqEvent){
				var checkedState = $(this).prop('checked');
				$('table.rawlinks_table_links input[type=checkbox]').prop('checked', checkedState);
			});
			
			// Go to bottom button
			$('#gobottom').on('click', function(){
				$('html, body').animate({
					scrollTop: document.body.scrollHeight || document.documentElement.scrollHeight,
				}, 500);
			});
			
			// Back to top button
			$('#backtop').on('click', function(){
				$('html, body').animate({
					scrollTop: 0,
				}, 500);
			});
		},
		
		/**
		 * Get table fields from server domain
		 * 
		 * @access public
		 * @method prototype
		 * @param String tableName
		 * @return void 
		 */
		getAjaxContent : function(targetSelect) { 
			// Table name
			var tableName = targetSelect.value;
			// Object to send to server
			var ajaxparams = { 
					idtask : 'loadTableFields',
					template : 'json',
					param: tableName
			     };
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams); 
			// Request JSON2JSON
			$.ajax({
		        type:"POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        data: {data : uniqueParam } , 
		        success: function(response)  {
		        	if(response !== null) {
		        		this.populateSelectFields(response, targetSelect); 
		        	}
	            }
			});   
		},
		
		/**
		 * Create, update or delete the single item priority chosen by combination
		 * between 2 dropdowns for item/priority
		 * 
		 * @access public
		 * @method prototype
		 * @param String action
		 * @param String type
		 * @return void 
		 */
		manageitemAjaxPriority : function(action, type) { 
			// Parameters to be sent to model
			var paramsObject = {};
			paramsObject.task = action;
			paramsObject.type = type;
			paramsObject.itemId = $('#paramsmenu_priorities, #paramscats_priorities').val();
			paramsObject.priorityValue = $('#priorities').val();
			
			// Validate values to submit
			if(action == 'store' && (!paramsObject.itemId || !paramsObject.priorityValue)) {
				$('#controls_grouper div.alert').remove();
				$('#controls_grouper').append('<div class="alert alert-priority alert-warning"><label class="glyphicon glyphicon-remove-circle"></label>' + COM_JMAP_PRIORITY_MAKE_SELECTIONS + '</div>');
				return false;
			}
			if(action == 'remove' && !paramsObject.itemId) {
				$('#controls_grouper div.alert').remove();
				$('#controls_grouper').append('<div class="alert alert-priority alert-warning"><label class="glyphicon glyphicon-remove-circle"></label>' + COM_JMAP_PRIORITY_CHOOSE_TO_DELETE + '</div>');
				return false;
			}
			
			// Object to send to server
			var ajaxparams = { 
					idtask : 'storeUpdatePriority',
					template : 'json',
					param: paramsObject
			     };
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams); 
			
			$('#controls_grouper').append('<img/>').children('img').attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif').css({
	            'position': 'absolute',
	            'margin': '15px 65px',
	            'width': '36px'
	        });
			
			// Request JSON2JSON
			$.ajax({
		        type:"POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        data: {data : uniqueParam } , 
		        success: function(response, textStatus, jqXHR)  {
		        	// Show a message to user about result of operation
		        	if(response) {
		        		if(response.result) {
		        			var messageClassName = 'success';
		        			
		        			// Manage dropdown states and user messages based on action button
		        			switch(action) {
			        			case 'store':
			        				var messageForUser = COM_JMAP_STORED_PRIORITY;
			        				// Manage adding text to item from priority stored text option
			        				var selectedPriority = $('#priorities option:selected').text();
			        				var currentTextForSelectedMenuitem = $('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').text();
			        				if($('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').hasClass('haspriority')) {
			        					currentTextForSelectedMenuitem = currentTextForSelectedMenuitem.slice(0, -6);
			        				}
			        				// Append and reassign
									$('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').text(currentTextForSelectedMenuitem + ' - ' + selectedPriority).addClass('haspriority');
			        				break;
			        				
			        			case 'remove':
			        				var messageForUser = COM_JMAP_DELETED_PRIORITY;
									// Manage remove text from item with deleted priority
			        				var selectedPriority = $('#priorities option:selected').text();
									var currentTextForSelectedMenuitem = $('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').text();
			        				// Append and reassign
									var currentTextForSelectedMenuitem = $('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').text();
									$('#paramsmenu_priorities option:selected, #paramscats_priorities option:selected').text(currentTextForSelectedMenuitem.slice(0, -6)).removeClass('haspriority').prop('selected', false);
			        				break;
		        			}
		        			
		        			var iconResponse = '<label class="glyphicon glyphicon-ok-circle"></label>';
		        		} else {
		        			// Something went wrong
		        			var messageClassName = 'warning';
		        			var messageForUser = COM_JMAP_ERROR_FOR_PRIORITY + response.errorMsg;
		        			var iconResponse = '<label class="glyphicon glyphicon-remove-circle"></label>';
		        		}
		        		
		        		$('#controls_grouper div.alert, #controls_grouper img').remove();
		        		$('#controls_grouper').append('<div class="alert alert-priority alert-' + messageClassName + '">' + iconResponse + messageForUser + '</div>');
		        		jqXHR.always(function() {
		        			setTimeout(function(){
		        				$('#controls_grouper div.alert').fadeOut();
		        			}, 800);
		        		});
		        	}
	            }
			});   
		},
		
		/**
		 * Get the single item priority and update the priority dropdown with retrieved value if any
		 * 
		 * @access public
		 * @method prototype
		 * @param String type
		 * @return void 
		 */
		getitemAjaxPriority : function(type) { 
			// Parameters to be sent to model
			var paramsObject = {};
			paramsObject.iditem = $('#paramsmenu_priorities, #paramscats_priorities').val();
			paramsObject.type = type;

			// Object to send to server
			var ajaxparams = { 
					idtask : 'getPriority',
					template : 'json',
					param: paramsObject
			     };
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams); 
			
			$('#controls_grouper').append('<img/>').children('img').attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif').css({
	            'position': 'absolute',
	            'margin': '15px 65px',
	            'width': '36px'
	        });
			
			// Request JSON2JSON
			$.ajax({
		        type:"POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        data: {data : uniqueParam } , 
		        success: function(response)  {
		        	// Update the priority dropdown
		        	if(!response.result) {
		        		$('#priorities').val('');
		        	}
		        	
		        	if(response.result && response.priority) {
		        		$('#priorities').val(response.priority);
		        	}
		        	
		        	// Remove progress
		        	$('#controls_grouper img').remove();
	            }
			});   
		},
		
		/**
		 * Populate multiple selects with retrieved fields for selected table
		 * 
		 * @access public
		 * @method prototype
		 * @param String tableName
		 * @return void 
		 */
		populateSelectFields : function(responseData, sourceElement) { 
			// Get target elements
			var sourceDataBindID = $(sourceElement).data('bind');
			var targetDataBindID = sourceDataBindID.replace('table', 'field');
			
			// Set target elements selector and empty current
			var targetElementsSelector = 'select[data-bind=' + targetDataBindID + ']';
			$(targetElementsSelector).empty();
			
			// Inject default option
			var currentOpt = $('<option value="">' + COM_JMAP_SELECTFIELD + '</option>');
			$(targetElementsSelector).append(currentOpt);
			
			$(responseData).each(function(index, item) {
				var currentOpt = $('<option value="' + item + '">' + item + '</option>');
				$(targetElementsSelector).append(currentOpt);
	  		}); 
			
			// Refresh style for target select
			$(targetElementsSelector).addClass('refreshfocus'); 
			setTimeout(function(){
				$(targetElementsSelector).removeClass('refreshfocus'); 
			}, 100);
		}
	}); 
	
	// Start JS application
	$.editSources = new Sources('select[data-bind^=table_]');
});