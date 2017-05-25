/**
 * Precaching client, this is the main application that interacts with server
 * side code for sitemap incremental generation and precaching process
 * 
 * @package JMAP::AJAXPRECACHING::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var XmlPrecaching = function() {
		/**
		 * Current active data sources for sitemap genaration
		 * The first async call to ajaxserver is mean to grab the full list
		 * of published data sources to process
		 * 
		 * @access private
		 * @var Array
		 */
		var dataSources;
		
		/**
		 * During the recursive async promise callback
		 * this represent the current processed data source id
		 * sent to server for precacher generation
		 * 
		 * @access private
		 * @var Int
		 */
		var currentProcessedDataSource;
		
		/**
		 * Number of links processed for a single data source
		 * during run processing status
		 * 
		 * @access private
		 * @var Int
		 */
		var currentProcessedLinks;
		
		/**
		 * Selected language if any from dropdown
		 * 
		 * @access private
		 * @var String
		 */
		var selectedLanguage;
		
		/**
		 * Status of the process
		 * 
		 * @access private
		 * @var String
		 */
		var processStatus;
		
		/**
		 * Status of the cycle and allowed running mode
		 * If ESC button pressed or process stopped by user
		 * the onCycle var is false and runProcessing is stopped
		 * 
		 * @access private
		 * @var String
		 */
		var onCycle;
		
		/**
		 * Target sitemap links parsed to be used for ajax sitemap generation
		 * 
		 * @access private
		 * @var Object
		 */
		var targetParsedSitemapLink;
		
		/**
		 * Store the clicked target generation button
		 * 
		 * @access private
		 * @var Object
		 */
		var targetGenerationButton;
		
		/**
		 * Iteration counter for recursive promise callback
		 * Server side process the iteration number to go step by step
		 * during sitemap generation
		 * 
		 * @access private
		 * @var Int
		 */
		var iterationCounter;
		
		/** Callbacks container array
		 * 
		 * @access private
		 * @var array
		 */
		var callbacksContainer;
		
		/**
		 * Start buttons for precaching process
		 * 
		 * @access private
		 * @var String
		 */
		var startButtons = 'label[data-role=startprecaching]';
		
		/**
		 * Snippet for clear button
		 * 
		 * @access private
		 * @var String
		 */ 
		var clearCacheButtons = '<button type="button" data-role="clearcache" data-loading-text="' + COM_JMAP_PRECACHING_CLEARING + '" class="btn btn-info btn-mini">' + COM_JMAP_PRECACHING_CLEAR_CACHE + '</button>';
		
		/**
		 * Inline user messages
		 * 
		 * @access private
		 * @var String
		 */
		var userMessageAlerts = '<div class="alert alert-danger"><span class="glyphicon glyphicon-exclamation-sign"></span><span class="alert-message"></span></div>';
		
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
			// Start the precaching process, first operation is enter the progress modal mode
			$(startButtons).on('click.precaching', function(jqEvent){
				showProgress(true, 20, 'standard', COM_JMAP_START_PRECACHING_PROCESS);
				targetGenerationButton = this;
				
				// Grab targeted sitemap link
				var tempTargetLink = $(this).parent().children('#jmap_seo input[data-role=sitemap_links]').val() || $(this).parent().children('#jmap_seo input[data-role=sitemap_links_sef]').attr('data-valuenosef');
				// Reset always sitemap params to merge dynamically
				targetParsedSitemapLink = {
						format:null,
						lang:null,
						dataset:null,
						Itemid:null
				}
				targetParsedSitemapLink = $.extend(targetParsedSitemapLink, parseURL(tempTargetLink).params);
			});
			
			// Live event binding only once on initialize, avoid repeated handlers and executed callbacks
			if(initialize) {
				// Language options change and Menu filter change
				$('#language_option, #menu_datasource_filters, #datasets_filters').on('change.precaching', function(jqEvent, isTriggered){
					// Check if event is fired by real UI and not jQuery programmatic trigger
					if(!isTriggered) {
						setPrecachedStatusLabels(jqEvent);
						// Set current language
						if($(this).attr('id') == 'language_option') {
							selectedLanguage = '/' + $(this).val() + '/';
						}
					}
				})
				
				// Live event binding for close button AKA stop process
				$(document).on('click.precaching', 'label.closeprecaching', function(jqEvent){
					$('#precaching_process').modal('hide');
				});
				
				// Live event binding for clear cache by ajax task
				$(document).on('click.precaching', 'button[data-role=clearcache]', function(jqEvent) {
					// Grab targeted sitemap link
					var tempTargetLink = $(this).parent().children('#jmap_seo input[data-role=sitemap_links]').val() || $(this).parent().children('#jmap_seo input[data-role=sitemap_links_sef]').attr('data-valuenosef');
					// Reset always sitemap params to merge dynamically
					targetParsedSitemapLink = {
							format:null,
							lang:null,
							dataset:null,
							Itemid:null
					}
					targetParsedSitemapLink = $.extend(targetParsedSitemapLink, parseURL(tempTargetLink).params);
					deletePrecachedFile(targetParsedSitemapLink, this);
				});
			}
		};
		
		/**
		 * Callbacks management queue
		 * It manages a queue structure for callbacks
		 * in FIFO fashion
		 * 
		 * @access private
		 * @return Object
		 */
		var callbacksQueue = function(fn) {
			// A new function is demanded to be added to queue
			if (typeof (fn) === 'function') {
				callbacksContainer.push(fn);
			} else {
				// No add mode, so get and return function in FIFO queue
				if(callbacksContainer.length) {
					var extractedCallback = callbacksContainer.splice(0, 1);
					return extractedCallback[0]();
				} else {
					// Return an empty anonymous function
					return function(){};
				}
			}
			
			// Return function object just added to queue
			return fn;
		};
		
		/**
		 * Show progress dialog bar with informations about the ongoing started process
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
				var modalDialog =	'<div class="modal fade" id="precaching_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_PRECACHING_TITLE + '</h4>' +
									        		'<label class="closeprecaching glyphicon glyphicon-remove-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_PRECACHING_PROCESS_RUNNING + '</p>' +
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
				$('#precaching_process').modal(modalOptions);
				
				// Async event progress showed and styling
				$('#precaching_process').on('shown.bs.modal', function(event) {
					$('#precaching_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					$(startButtons).off('.precaching').addClass('disabled');
					
					// Add an async event in the next cycle
					setTimeout(function(){
						// Start fetching data sources server side
						callbacksQueue();
					}, 500);
				});
				
				// Remove backdrop after removing DOM modal
				$('#precaching_process').on('hidden.bs.modal',function(jqEvent){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Reset callbacks container
					callbacksContainer = new Array();
					callbacksQueue(getDataSources);
					callbacksQueue(runProcessing);
					
					// Stop recursive promise callback
					onCycle = false;
					
					// Rebind events to button
					setTimeout(function(){
						addListeners(false);
						$(startButtons).removeClass('disabled');
					}, 3500)
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
						$('#precaching_process').modal('hide');
					}, 3500);
				}
			}
		}
		
		/**
		 * Main recursive callback based on promises
		 * This function is called everytime a promise is successfully resolved,
		 * until the retrieved data sources are ended without errors and no more
		 * data to process are still available
		 * 
		 * @access private
		 * @return Void
		 */
		var runProcessing = function() {
			// Commit ajax request, if rows processed > 0 go on with this data source, otherwise increment data source if any, otherwise process has completed 
			var postedParams = {
				iteration_counter : iterationCounter,
				datasource_id : currentProcessedDataSource.id,
				process_status : processStatus,
				format : targetParsedSitemapLink.format,
				lang: targetParsedSitemapLink.lang,
				dataset: targetParsedSitemapLink.dataset,
				Itemid: targetParsedSitemapLink.Itemid
			};

			// Request JSON2JSON
			var iterationPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../index.php" + selectedLanguage + "?option=com_jmap&task=sitemap.doPreCaching",
					dataType : 'json',
					context : this,
					data : postedParams
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message + ' - Context:' + data.context, true);
						return false;
					}
					
					// Data source has no affected rows, so finished, check if other data sources are available and go on
					if(!parseInt(data.affected_rows)) {
						defer.reject('<p>' + COM_JMAP_PRECACHING_DATA_SOURCE_COMPLETED + currentProcessedDataSource.name + '</p>', false, data);
						return false;
					}
					
					// If user has stopped processing alt execution
					if(!onCycle) {
						defer.reject('<p>' + COM_JMAP_PRECACHING_INTERRUPT + '</p>', true);
						return false;
					}
					
					// Check response all went well
					if(data.result && !!parseInt(data.affected_rows)) {
						defer.resolve(data.affected_rows);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown, true);
				});
			}).promise();

			iterationPromise.then(function(responseData) {
				// Do stuff
				iterationCounter++;
				
				// Update process status
				currentProcessedLinks += parseInt(responseData);
				var statusMessage = '<p><span class="label label-primary">' + COM_JMAP_PRECACHING_REPORT_DATASOURCE + 
									'<span class="badge">' + currentProcessedDataSource.name + '</span></span></p>' +
									'<p><span class="label label-primary">' + COM_JMAP_PRECACHING_REPORT_DATASOURCE_TYPE + 
									'<span class="badge">' + currentProcessedDataSource.type + '</span></span></p>' +
									'<p><span class="label label-primary">' + COM_JMAP_PRECACHING_REPORT_LINKS + 
									'<span class="badge">' + currentProcessedLinks + '</span></span></p>';
				showProgress(false, 100, 'striped', statusMessage);
				
				// Run recursive promise callback on next data source
				processStatus = 'run';
				runProcessing();
			}, function(errorText, exception, data) {
				// Real exception detected, so abort processing and exit
				if(exception) {
					showProgress(false, 100, 'normal', errorText, 'progress-bar-danger');
					// Prepare status for next started processing
					processStatus = 'start';
					iterationCounter = 0;
					currentProcessedLinks = 0;
				} else {
					showProgress(false, 100, 'striped', errorText);
					// Data source has terminated, check if other data sources are available to process otherwise processing finished
					// Run recursive promise callback on next data source
					if(dataSources.length) {
						processStatus = 'run';
						iterationCounter = 0;
						currentProcessedLinks = 0;
						currentProcessedDataSource = dataSources.pop();
						runProcessing();
					} else {
						// Last ajax call status = end to close </urlset>
						if(processStatus == 'run' || processStatus == 'start') {
							showProgress(false, 100, 'striped', COM_JMAP_PRECACHING_PROCESS_FINALIZING);
							processStatus = 'end';
							
							runProcessing();
						} else {
							// Process has been completed, no more data sources available in the stack
							showProgress(false, 100, 'normal', COM_JMAP_PRECACHING_PROCESS_COMPLETED, 'progress-bar-success');
							
							// Prepare status for next started processing
							processStatus = 'start';
							iterationCounter = 0;
							currentProcessedLinks = 0;
							
							// Refresh label status, successfully cached
							$(targetGenerationButton).parent().children('span.label')
															  .removeClass('label-danger')
															  .addClass('label-success')
															  .html(COM_JMAP_PRECACHING_CACHED + '<br/>' + data.lastgeneration);
							
							// Add clear delete button here after
							$(targetGenerationButton).parent(':not(:has(button[data-role=clearcache]))').append(clearCacheButtons);
							
							// Close progress bar
							setTimeout(function(){
								$('#precaching_process').modal('hide');
							}, 3500);
						}
					}
				}
			});
		};
		
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @return Void
		 */
		var getDataSources = function() {
			// Object to send to server
			var ajaxparams = {
				idtask : 'loadDataSources',
				template : 'json',
				param: {}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			// Request JSON2JSON
			var dataSourcePromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
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
					
					// No data sources found
					if(!data.datasources.length) {
						defer.reject(COM_JMAP_PRECACHING_NO_DATASOURCES_FOUND, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result && data.datasources.length) {
						defer.resolve(data.datasources);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			dataSourcePromise.then(function(responseData) {
				// Do stuff
				dataSources = responseData.reverse();
				// Pop the first data source retrieved
				currentProcessedDataSource = dataSources.pop();
				
				// Update process status, we started
				showProgress(false, 100, 'striped active', COM_JMAP_PRECACHING_DATASOURCES_RETRIEVED);
				
				// Start recursive promise callback
				onCycle = true;
				callbacksQueue();
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'progress-bar-danger');
			});
		};
		
		/**
		 * Get informations from server side about precached sitemaps
		 * 
		 * @access private
		 * @return Void
		 */
		var setPrecachedStatusLabels = function() {
			// Grab all links and build as array to post
			var availableSitemapLinks = new Array();
			if($('#jmap_seo input[data-role=sitemap_links]').length) {
				var availableSitemapLinksWrappedSet = $('#jmap_seo input[data-role=sitemap_links]').slice(1, 7);
				$(availableSitemapLinksWrappedSet).each(function(index, value){
					availableSitemapLinks[index] = $(value).val();
				});
			} else {
				//SEF links mode detected
				var availableSitemapLinksWrappedSet = $('#jmap_seo input[data-role=sitemap_links_sef]').slice(1, 7);
				$(availableSitemapLinksWrappedSet).each(function(index, value){
					availableSitemapLinks[index] = $(value).attr('data-valuenosef');
				});
			}
			
			// Object to send to server
			var ajaxparams = {
				idtask : 'getPrecachedSitemaps',
				template : 'json',
				param: availableSitemapLinks
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			// Request JSON2JSON
			var statusLabelsPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
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
					
					defer.resolve(data.sitemapLinksStatus);
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					if(errorThrown) {
						var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
						defer.reject('-' + genericStatus + '- ' + errorThrown);
					}
				});
			}).promise();

			statusLabelsPromise.then(function(responseData) {
				$.each(responseData, function(url, value){
					if(value.cached) {
						// Set label to cached
						$('input[value="' + url + '"], input[data-valuenosef="' + url + '"]')
													   	.parent()
													   	.children('span.label')
													   	.removeClass('label-danger')
													   	.addClass('label-success')
													   	.html(COM_JMAP_PRECACHING_CACHED + '<br/>' + value.lastgeneration);
						// Append clear cache button if not exists
						$('input[value="' + url + '"], input[data-valuenosef="' + url + '"]')
														.parent(':not(:has(button[data-role=clearcache]))')
														.children('span.label')
														.after(clearCacheButtons);
					} else {
						// Set label to not cached
						$('input[value="' + url + '"], input[data-valuenosef="' + url + '"]')
														.parent()
														.children('span.label')
														.removeClass('label-success')
														.addClass('label-danger')
														.html(COM_JMAP_PRECACHING_NOT_CACHED);
						// Remove clear cache buttons
						$('input[value="' + url + '"], input[data-valuenosef="' + url + '"]')
														.parent()
														.children('button[data-role=clearcache]')
														.remove();
					}
				});
				
			}, function(errorText, error) {
				// Show an error message retrieving precaching status
				$('#system-message-container').html(userMessageAlerts);
				$('#system-message-container span.alert-message').text(errorText);
				setTimeout(function(){
					$('#system-message-container').slideUp(function(jqEvent){
						$(this).empty().show();
					});
				}, 3000);
			});
		};
		
		/**
		 * Delete precached sitemap files on server on demand
		 * 
		 * @access private
		 * @return Void
		 */
		var deletePrecachedFile = function(linksInformations, btn) {
			// Change button status during processing
			$(btn).button('loading');
			// Object to send to server
			var ajaxparams = {
				idtask : 'deletePrecachedSitemap',
				template : 'json',
				param: linksInformations
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			// Request JSON2JSON
			var deleteCachedSitemapPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
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
					
					defer.resolve();
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				}).always(function(){
					// Change button status during processing
					$(btn).button('reset');
				});
			}).promise();

			deleteCachedSitemapPromise.then(function(responseData) {
				// Ensure label has no precached state
				$(btn).prev().removeClass('label-success').addClass('label-danger').html(COM_JMAP_PRECACHING_NOT_CACHED);
				$(btn).remove();
			}, function(errorText, error) {
				// Show an error message deleting precaching file
				$('#system-message-container').html(userMessageAlerts);
				$('#system-message-container span.alert-message').text(errorText);
				setTimeout(function(){
					$('#system-message-container').slideUp(function(jqEvent){
						$(this).empty().show();
					});
				}, 3000);
			});
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
			// Initialize container
			callbacksContainer = new Array();
			callbacksQueue(getDataSources);
			callbacksQueue(runProcessing);
			
			// Reset counters
			iterationCounter = 0;
			currentProcessedLinks = 0;
			
			// Initialize process status
			processStatus = 'start';
			onCycle = false;

			// Add UI events
			addListeners.call(this, true);
			
			// Initialize as empty to avoid JS errors
			targetParsedSitemapLink = {
					format:null,
					lang:null,
					dataset:null,
					Itemid:null
			}
			
			// No multilanguage cases
			selectedLanguage = '';
			// Set current language if any
			if($('#language_option').length) {
				selectedLanguage = '/' + $('#language_option').val() + '/';
			}
			
			// Start grabbing informations about precached sitemaps links
			setPrecachedStatusLabels();
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapXmlPrecaching = new XmlPrecaching();
	});
})(jQuery);