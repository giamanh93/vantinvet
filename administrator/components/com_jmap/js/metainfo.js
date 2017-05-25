/**
 * Meta info manager
 * 
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var Metainfo = function() {
		/**
		 * Target sitemap link
		 * 
		 * @access private
		 * @var String
		 */
		var targetSitemapLink = null;
		
		/**
		 * Message timeout handler
		 * 
		 * @access private
		 * @var Object
		 */
		var msgTimeout = null;
		
		/**
		 * Promises array
		 * 
		 * @access private
		 * @var Array
		 */
		var promisesCollection = new Array();
		
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		var showMessages = function(message, state) {
			var messageSnippet = '<div id="jmap_alert_message" class="alert alert-' + state + '">' +
						            '<span class="glyphicon glyphicon-info-sign"></span><span class="alert-message"> ' + message + '</span>' +
						          '</div>';		
			
			clearTimeout(msgTimeout);
			$('#jmap_alert_message').remove();
			
			$('#alert_append').append(messageSnippet);
			$('#jmap_alert_message').fadeIn('fast');
			
			timerReady = $.Deferred();
			$.when(timerReady).done(function(response){
				$('#jmap_alert_message').fadeOut('fast', function(){
					$('#jmap_alert_message').remove();
				});
			});
			
			msgTimeout = setTimeout(function(){
				timerReady.resolve();
			}, 3000);
		};
		
		/**
		 * Parse url to grab query string params to post to server side for sitemap generation
		 * 
		 * @access private
		 * @return Object
		 */
		var parseURL = function(url) {
		    var a =  document.createElement('a');
		    a.href = url;
		    return {
		        source: url,
		        protocol: a.protocol.replace(':',''),
		        host: a.hostname,
		        port: a.port,
		        query: a.search,
		        params: (function(){
		            var ret = {},
		                seg = a.search.replace(/^\?/,'').split('&'),
		                len = seg.length, i = 0, s;
		            for (;i<len;i++) {
		                if (!seg[i]) { continue; }
		                s = seg[i].split('=');
		                ret[s[0]] = s[1];
		            }
		            return ret;
		        })(),
		        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],
		        hash: a.hash.replace('#',''),
		        path: a.pathname.replace(/^([^\/])/,'/$1'),
		        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],
		        segments: a.pathname.replace(/^\//,'').split('/')
		    };
		}
		
		/**
		 * Register user events for interface controls
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var addListeners = function(initialize) {
			// Start the generation process, first operation is enter the progress modal mode
			$('a.jmap_metainfo').on('click.metainfo', function(jqEvent){
				// Prevent click link default
				jqEvent.preventDefault();
				
				// Show striped progress started generation
				showProgress(true, 50, 'striped', COM_JMAP_METAINFO_STARTED_SITEMAP_GENERATION);
				
				// Grab targeted sitemap link
				targetSitemapLink = $(this).attr('href');
			});
			
			// Register form submit event
			$('#adminForm ul.pagination-list li').filter(function(){
				if($(this).hasClass('active') || $(this).hasClass('disabled')) {
					return false;
				}
				return true;
			}).on('click.metainfo', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			$('#limit').on('change.metainfo', function(jqEvent){
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			$('#adminForm table.adminlist th a.hasTooltip').on('click.metainfo', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			
			// Register button task actions
			// Save metainfo
			$('#adminForm button[data-action=savemeta]').on('click.metainfo', function(jqEvent) {
				// Prevent button default
				jqEvent.preventDefault();
				
				// Retrive information to save
				var rowIdentifier = $(this).data('save');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var metaTitle = $('textarea[data-titleidentifier=' + rowIdentifier + ']').val();
				var metaDesc = $('textarea[data-descidentifier=' + rowIdentifier + ']').val();
				var metaImage = $('input[data-mediaidentifier=' + rowIdentifier + '], #jform_media_identifier_' + rowIdentifier).val();
				var robotsDirective = $('select[data-robotsidentifier=' + rowIdentifier + ']').val();
				var publishedStatus = $('input[name=published' + (rowIdentifier-1) + ']:checked').prop('value');
				var excludedStatus = $('input[name=excluded' + (rowIdentifier-1) + ']:checked').prop('value');

				// Perform validation, at least one of title/desc/robots
				// must be a valid value
				if (!metaTitle && !metaDesc && !robotsDirective && !metaImage) {
					showMessages(COM_JMAP_METAINFO_SET_ATLEAST_ONE, 'warning');
					return false;
				}

				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					meta_title : metaTitle,
					meta_desc : metaDesc,
					meta_image : metaImage,
					robots : robotsDirective,
					published : publishedStatus,
					excluded : excludedStatus
				};
				
				// Now save to server side
				saveDataStatus('saveMeta', dataObject);
				
				return false;
			});
			
			// Delete metainfo record
			$('#adminForm button[data-action=deletemeta]').on('click.metainfo', function(jqEvent) {
				// Prevent button default
				jqEvent.preventDefault();
				
				// Retrive information to save
				var rowIdentifier = $(this).data('delete');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier
				};
				
				// Now save to server side
				saveDataStatus('deleteMeta', dataObject);
				
				// Now reset row data
				var parentRow = $(this).parents('tr');
				$('textarea.metainfo, select.robots_directive, input.mediaimagefield', parentRow).val('');
				$('textarea.metainfo', parentRow).text('');
				rowButtonStatus(parentRow);
				
				// Now reset characters counter
				$('span.labelmetainfo', parentRow).text( COM_JMAP_CHARACTERS + 0);
				
				// Finally reset the exclude state in the case there was one. Take care that the record is completely deleted
				$('fieldset[data-action=excludedmeta] div.controls label:first-child', parentRow).trigger('click', [true]);
				
				return false;
			});
			
			// Change metainfo record state
			$('#adminForm fieldset[data-action=statemeta] label').on('click.metainfo', function(jqEvent, noTrigger) {
				// Avoid triggering if not user click
				if(noTrigger) {
					return false;
				}
				
				// Retrive information to save
				var rowIdentifier = $(this).parents('fieldset').data('state');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var publishedState = $(this).children('input').val();
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					field : 'published',
					fieldValue : parseInt(publishedState)
				};
				
				// Now save to server side
				saveDataStatus('stateMeta', dataObject);
				
				return false;
			});
			
			$('#adminForm fieldset[data-action=excludedmeta] label').on('click.metainfo', function(jqEvent, noTrigger) {
				// Avoid triggering if not user click
				if(noTrigger) {
					return false;
				}
				
				// Retrive information to save
				var parentRow = $(this).parents('tr');
				var rowIdentifier = $(this).parents('fieldset').data('state');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var excludedState = $(this).children('input').val();
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					field : 'excluded',
					fieldValue : parseInt(excludedState) 
				};
				
				// Now save to server side
				saveDataStatus('stateMeta', dataObject);
				
				// Switch by default to unpublished if it's a new empty record store
				if($('fieldset[data-action=statemeta] div.controls label:first-child', parentRow).hasClass('jmap_inactive')) {
					$('fieldset[data-action=statemeta] div.controls label:first-child', parentRow).trigger('click', [true]);
				}
				
				return false;
			});
			
			// Bind an event handler for textarea writing, used to count characters and lock/unlock disabled buttons
			$('table.adminlist tbody textarea, table.adminlist tbody input').on('keyup.metainfo', function(jqEvent) {
				var parentRow = $(this).parents('tr');
				// Update notify button status callback
				rowButtonStatus(parentRow);
			});
			
			$('table.adminlist tbody input').on('change.metainfo input.metainfo propertychange.metainfo', function(jqEvent) {
				var parentRow = $(this).parents('tr');
				// Update notify button status callback
				rowButtonStatus(parentRow);
			});
			
			// Required for Joomla 3.5+
			$('table.adminlist tbody a.button-clear').on('click.metainfo', {context:this}, function(jqEvent) {
				var parentRow = $(this).parents('tr');
				var indentifier = $('td.link_loc a').data('linkidentifier');
				// Update notify button status callback
				setTimeout(function(context){
					context.refreshRowStatus(parentRow, indentifier);
				}, 1, jqEvent.data.context)
			});
				
			$('table.adminlist tbody select.robots_directive').on('change.metainfo', function(jqEvent) {
				var parentRow = $(this).parents('tr');
				// Update notify button status callback
				rowButtonStatus(parentRow);
			});

			// Live event binding only once on initialize, avoid repeated
			// handlers and executed callbacks
			if (initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.metainfo', 'label.closeprecaching', function(jqEvent) {
					$('#metainfo_process').modal('hide');
				});
			}
			
			// Attach auto populate event listener
			$('#toolbar-database button').on('click', function(jqEvent){
				jqEvent.preventDefault();
			
				// Start processing
				autoPopulateMetainfo();
				
				return false;
			});
		};
		
		/**
		 * Show progress dialog bar with informations about the ongoing started
		 * process
		 * 
		 * @access private
		 * @return Void
		 */
		var showProgress = function(isNew, percentage, type, status, classColor) {
			// No progress process injected
			if(isNew) {
				// Show second progress
				var progressBar = '<div class="progress progress-' + type + ' active">' +
										'<div id="progress_bar" class="progress-bar" role="progressbar" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100">' +
											'<span class="sr-only"></span>' +
										'</div>' +
									'</div>';
				
				// Build modal dialog
				var modalDialog =	'<div class="modal fade" id="metainfo_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_METAINFO_TITLE + '</h4>' +
									        		'<label class="closeprecaching glyphicon glyphicon-remove-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_METAINFO_PROCESS_RUNNING + '</p>' +
								        		'</div>' +
								        		'<div class="modal-body">' +
									        		'<p>' + progressBar + '</p>' +
									        		'<p id="progress_info">' + status + '</p>' +
								        		'</div>' +
								        		'<div class="modal-footer">' +
									        	'</div>' +
								        	'</div><!-- /.modal-content -->' +
							        	'</div><!-- /.modal-dialog -->' +
							        '</div>';
				// Inject elements into content body
				$('body').append(modalDialog);
				
				// Setup modal
				var modalOptions = {
						backdrop:'static'
					};
				$('#metainfo_process').modal(modalOptions);
				
				// Async event progress showed and styling
				$('#metainfo_process').on('shown.bs.modal', function(event) {
					$('#metainfo_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					
					// Start AJAX GET request for sitemap generation in the cache folder
					startSitemapCaching(targetSitemapLink);
				});
				
				// Remove backdrop after removing DOM modal
				$('#metainfo_process').on('hidden.bs.modal',function(jqEvent){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Redirect to MVC core cpanel, discard metainfo process
					window.location.href = 'index.php?option=com_jmap&task=cpanel.display'
				});
			} else {
				// Refresh only status, progress and text
				$('#progress_bar').addClass(classColor)
								  .css({'width':percentage + '%'});
				
				$('#progress_bar').parent().removeClass('progress-normal progress-striped')
								  .addClass('progress-' + type);
				
				$('#progress_info').html(status);		
				
				// An error has been detected, so auto close process and progress bar
				if(classColor == 'progress-bar-danger') {
					setTimeout(function(){
						$('#metainfo_process').modal('hide');
					}, 3500);
				}
			}
		}
		
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @param String targetSitemapLink
		 * @return Void
		 */
		var startSitemapCaching = function(targetSitemapLink) {
			// No ajax request if no control panel generation in 2 steps
			if(!targetSitemapLink) {
				return;
			}
			// Request JSON2JSON
			var dataSourcePromise = $.Deferred(function(defer) {
				$.ajax({
					type : "GET",
					url : targetSitemapLink,
					dataType : 'json',
					context : this,
					data: {'metainfojsclient' : true}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(COM_JMAP_METAINFO_ERROR_STORING_FILE, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						defer.resolve();
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			dataSourcePromise.then(function() {
				// Update process status, we started
				showProgress(false, 100, 'striped', COM_JMAP_METAINFO_GENERATION_COMPLETE, 'progress-normal');
				
				// Parse sitemap parameters
				var sitemapParams = parseURL(targetSitemapLink).params;
				var sitemapLang = sitemapParams.lang ? '&sitemaplang=' + sitemapParams.lang : '';
				var sitemapDataset = sitemapParams.dataset ? '&sitemapdataset=' + sitemapParams.dataset : '';
				var sitemapMenuID = sitemapParams.Itemid ? '&sitemapitemid=' + sitemapParams.Itemid : '';
				
				// Redirect to MVC core
				window.location.href = 'index.php?option=com_jmap&task=metainfo.display&metainfojsclient=1' + sitemapLang + sitemapDataset + sitemapMenuID;
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'progress-bar-danger');
			});
		};
		
		/**
		 * Manage the data saving and the status change for each sitemap record
		 * in the model database table
		 * 
		 * @access private
		 * @param String action
		 * @return Void
		 */
		var saveDataStatus = function(action, dataObject) {
			// Object to send to server
			var ajaxparams = {
				idtask : action,
				param: dataObject
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Request JSON2JSON
			var metainfoPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						var userMessage = data.exception_message || COM_JMAP_METAINFO_SAVED;
						defer.resolve(userMessage, data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			metainfoPromise.then(function(message, dataResponse) {
				// Update process status, we started
				if(action == 'stateMeta' && dataResponse.result && dataResponse.exception_message) { } else {
					showMessages(message, 'success');
				}
			}, function(errorText, error) {
				// Do stuff and exit
				showMessages(errorText, 'error');
			});
		};
		
		/**
		 * Evaluate and keep synced the enabled buttons status based on the current row values
		 * 
		 * @access private
		 * @param specificRow
		 * @return Void
		 */
		var rowButtonStatus = function(specificRow) {
			// Search for each row if at least one of the 3 values is specified and only in that case enable buttons
			var finalSelector = specificRow || 'table.adminlist tbody tr';
			
			$(finalSelector).each(function(index, tableRow){
				var titleValue = $('textarea.metatitle', tableRow).val();
				var descValue = $('textarea.metadesc', tableRow).val();
				var imageValue = $('input.mediaimagefield', tableRow).val();
				var robotsValue = $('select.robots_directive', tableRow).val();
				
				// If none of the value are available disable buttons
				if(!titleValue && !descValue && !imageValue && !robotsValue) {
					$('button, fieldset.radio[data-action=statemeta] label', tableRow).attr('disabled', true);
					$('fieldset.radio[data-action=statemeta] label', tableRow).addClass('jmap_inactive');
				} else {
					$('button, fieldset.radio[data-action=statemeta] label', tableRow).removeAttr('disabled');
					$('fieldset.radio[data-action=statemeta] label', tableRow).removeClass('jmap_inactive');
				}
			});
		};
		
		/**
		 * Extend the jQuery prototype with a plugin to count and validate
		 * the length of the textarea characters
		 */
		var addTextareaLengthvalidation = function() {
			// jQuery prototype
			$.fn.jmapCharacterCount = function charCount(options) {
				var defaults = {
						limit : 100
				};
				var options = $.extend({}, defaults, options);
				if (this.length) {
					return $(this).each(function charCountEachElement() {
						var $this = $(this);
						var addElements = function() {
							$this.container    = $('<span/>');
							$this.counter      = $('<span/>').addClass('label labelmetainfo').addClass('label-primary').text( COM_JMAP_CHARACTERS + 0);
							
							$this.after($this.container);
							$this.container.append($this.counter);
						};
						$this.update = function() {
							var length = $this.val().length;
							$this.counter.text(COM_JMAP_CHARACTERS + length);
							if (options.limit < length) {
								$this.counter.removeClass('label-primary');
								$this.counter.addClass('label-danger');
							} else {
								$this.counter.removeClass('label-danger');
								$this.counter.addClass('label-primary');
							}
						};
						$this.on('keyup.metainfo', function(event) {
							$this.update();
						});
						addElements();
						$this.update();
					});
				}
			};
		};
		
		/**
		 * Process the asyncronous analysis of links
		 * Auto populates the metainfo links records providing the auto click on the save button
		 *
		 * @access private
		 * @return Void
		 */
		var autoPopulateMetainfo = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link]');
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				var targetCrawledLink = $('a[data-role="link"]').get(index);
				var targetTitle = $('textarea[data-bind="{title}"]').get(index);
				var targetDesc = $('textarea[data-bind="{desc}"]').get(index);
				
				// Preappend a loading progress waiter to each processed link
				$(targetCrawledLink).after('<img class="metainfo_loader" width="16px" height="16px" src="' + jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif' + '"/>');
				
				promisesCollection[index] = $.Deferred(function(defer) {
					setTimeout(function(){
						$.ajax({
							type : "GET",
							url : $(link).attr('href'),
						}).done(function(data, textStatus, jqXHR) {
							// Check response HTTP status code
							defer.resolve(data, jqXHR.status);
						}).fail(function(jqXHR, textStatus, errorThrown) {
							// Error found
							defer.resolve(null, jqXHR.status);
						});
					}, index * jmap_crawlerDelay);
				}).promise();
				
				promisesCollection[index].then(function(responseData, status) {
					// Always remove the waiter
					$(targetCrawledLink).next('img.metainfo_loader').remove();
					
					// STEP 1 - If the status validation is not 200 OK simply skip to next link promise
					if(status != 200) {
						return;
					}
					
					// Set init status for this row iteration
					var titlePopulated = false;
					var descPopulated = false;
					
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());

					// STEP 2 - Title retrieval and reporting
					var title = responseDataWrappedSet.filter('title').text().trim();
					// Remove the site name suffix/prefix part in the title if any
					if(jmap_siteNamePageTitles > 0) {
						switch (jmap_siteNamePageTitles) {
							// Prepended before prefix
							case 1:
								title = title.replace(jmap_siteName + ' - ', '');
								break;
							
							// Appended after suffix
							case 2:
								title = title.replace(' - ' + jmap_siteName, '');
								break;
						}
					}
					
					// Manage title validity, preserve existing values
					if(title && !$(targetTitle).text()) {
						$(targetTitle).text( title ).val( title );
						titlePopulated = true;
					}
					
					// STEP 3 - Description retrieval and reporting
					var description = responseDataWrappedSet.filter('meta[name=description]').attr('content') || '';
					description = description.trim();
					// Manage description validity, preserve existing values
					if(description && !$(targetDesc).text()) {
						$(targetDesc).text( description ).val( description );
						descPopulated = true;
					}
					
					// Now trigger the change event for this row
					if(titlePopulated || descPopulated) {
						$(targetTitle).trigger('keyup.metainfo');
						$(targetDesc).trigger('keyup.metainfo');
						// Finally trigger the save data button to persist
						var targetSaveBtn = $('button[data-action=savemeta]').get(index);
						$(targetSaveBtn).trigger('click.metainfo');
					}
				});
			});
		};
		
		/**
		 * Request a refresh of the row status
		 * 
		 * @access public
		 * @param Object parentRow
		 * @param Integer identifier
		 * @return Void
		 */
		this.refreshRowStatus = function(parentRow, identifier) {
			rowButtonStatus(parentRow);
			
			// Check if it's a deletion meta image after a button click in the media widget
			var titleValue = $('textarea.metatitle', parentRow).val();
			var descValue = $('textarea.metadesc', parentRow).val();
			var imageValue = $('input.mediaimagefield', parentRow).val();
			var robotsValue = $('select.robots_directive', parentRow).val();
			
			// If none of the values are available AKA empty record delete on server
			if(!titleValue && !descValue && !imageValue && !robotsValue) {
				// Retrive information to save
				var linkIdentifier = $('a[data-linkidentifier=' + identifier + ']').attr('href');
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier
				};
				
				// Now save to server side
				saveDataStatus('deleteMeta', dataObject);
			}
		};
		
		/**
		 * Function dummy constructor
		 * 
		 * @access private
		 * @param String
		 *            contextSelector
		 * @method <<IIFE>>
		 * @return Void
		 */
		(function __construct() {
			// Search for each row if at least one of the 3 values is specified and only in that case enable buttons
			rowButtonStatus();
			
			// Extend jQuery with plugin
			addTextareaLengthvalidation();
			
			$('table.adminlist tbody textarea.metatitle').jmapCharacterCount({
	            limit: 60
	        });
			
			$('table.adminlist tbody textarea.metadesc').jmapCharacterCount({
	            limit: 160
	        });
			
			// Fix for Joomla 3.5 modals
			$('td.metaimage a.button-select').on('click', function(jqEvent) {
				$('button[data-dismiss=modal]').removeAttr('disabled');
			})
			$('td.metaimage input.field-media-input.mediaimagefield').removeAttr('readonly');
			
			$('#toolbar-database button').removeAttr('onclick');
			 
			// Add UI events
			addListeners.call(this, true);
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapMetainfo = new Metainfo();
	});
})(jQuery);