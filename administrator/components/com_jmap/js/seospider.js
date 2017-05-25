/**
 * Spider client for SEO reports and issues reporter
 * 
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var SeoSpider = function() {
		/**
		 * Target sitemap link
		 * 
		 * @access private
		 * @var String
		 */
		var targetSitemapLink = null;
		
		/**
		 * Promises array
		 * 
		 * @access private
		 * @var Array
		 */
		var promisesCollection = new Array();
		
		/**
		 * Titles collection
		 * 
		 * @access private
		 * @var Object
		 */
		var titlesCollection = {};
		
		/**
		 * Descriptions collection
		 * 
		 * @access private
		 * @var Object
		 */
		var descriptionsCollection = {};
		
		/**
		 * Timeout reference for arrows
		 * 
		 * @access private
		 * @var Object
		 */
		var arrowsTimeout = null;
		
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
		 * Generate an unique hash for a title or description string in input
		 * 
		 * @access private
		 * @return Object
		 */
		var generateHash = function(string) {
			 var hash = 0, i, chr, len;
			  if (string.length == 0) return hash;
			  for (i = 0, len = string.length; i < len; i++) {
				  chr   = string.charCodeAt(i);
				  hash  = ((hash << 5) - hash) + chr;
				  hash |= 0; // Convert to 32bit integer
			  }
			  return hash;
		};
		
		/**
		 * Register user events for interface controls
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var addListeners = function(initialize) {
			// Start the precaching process, first operation is enter the progress modal mode
			$('a.jmap_seospider').on('click.seospider', function(jqEvent){
				// Prevent click link default
				jqEvent.preventDefault();
				
				// Show striped progress started generation
				showProgress(true, 50, 'striped', COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION);
				
				// Grab targeted sitemap link
				targetSitemapLink = $(this).attr('href');
			});
			
			// Register form submit event
			$('#adminForm ul.pagination-list li').filter(function(){
				if($(this).hasClass('active') || $(this).hasClass('disabled')) {
					return false;
				}
				return true;
			}).on('click.seospider', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			$('#adminForm select[class!=noanalyzer]').on('change.seospider', function(jqEvent){
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			$('#adminForm table.adminlist th a.hasTooltip').on('click.seospider', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			
			// Live event binding only once on initialize, avoid repeated handlers and executed callbacks
			if(initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.seospider', 'label.closeprecaching', function(jqEvent){
					$('#seospider_process').modal('hide');
				});
			}
			
			// Append a dialog with links list detail
			$('div[data-bind="{title-duplicates}"], div[data-bind="{desc-duplicates}"]').on('click.seospider', function(jqEvent){
				// Ensure to not execute noduplicates badge
				if($(this).hasClass('noduplicates')) {
					return false;
				}
				
				// Remove any previous instance
				$('#details_dialog').remove();
				
				var dialogTitle = '';
				var dialogContents = new Array();
				var thisLinkToSkip = $(this).data('link');
				var thisTitleHash = $(this).data('titlehash');
				var thisDescriptionHash = $(this).data('descriptionhash');
				var didascaly = '';
				
				// Determine the type of the dialog and title
				var thisBind = $(this).data('bind');
				switch(thisBind) {
					case '{title-duplicates}':
						dialogTitle = COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_TITLE;
						dialogContents = titlesCollection[thisTitleHash];
						didascaly = COM_JMAP_SEOSPIDER_TITLE_DETAILS + $(this).parents('tr').find('div[data-bind="{title}"] div.seospider_textlabel').text();
						break;
					
					case '{desc-duplicates}':
						dialogTitle = COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_DESCRIPTION;
						dialogContents = descriptionsCollection[thisDescriptionHash];
						didascaly = COM_JMAP_SEOSPIDER_DESCRIPTION_DETAILS + $(this).parents('tr').find('div[data-bind="{desc}"] div.seospider_textlabel').text();
						break;
				}
				showDuplicatesDetails(dialogTitle, dialogContents, didascaly, thisLinkToSkip);
			});
			
			// Closer dialog button
			$(document).on('click.seospider', 'label.closedialog', function(jqEvent){
				$('#details_dialog').remove();
			});
			
			// Link duplicate with scroller
			$(document).on('click.seospider', 'li.seospider_duplicate a, a.seospider_duplicate', function(jqEvent){
				// Reset timeout if any
				if(typeof(arrowsTimeout) !== 'undefined') {
					clearTimeout(arrowsTimeout);
				}
				
				var anchorTarget = $(this).attr('href');
				var elementTarget = $('a[data-role="link"][href="' + anchorTarget + '"]');
				if(elementTarget.length) {
					$('html, body').animate({
						scrollTop: elementTarget.offset().top - 95
					}, 500);
				}
				// Append an indicator arrow
				$(elementTarget).next('span.seospider_indicator').remove();
				$(elementTarget).after('<span class="seospider_indicator glyphicon glyphicon-circle-arrow-left"></span>');
				arrowsTimeout = setTimeout(function(){
					$('span.seospider_indicator').remove();
				}, 3500);
				
				return false;
			});
			
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
				var modalDialog =	'<div class="modal fade" id="seospider_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_SEOSPIDER_TITLE + '</h4>' +
									        		'<label class="closeprecaching glyphicon glyphicon-remove-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_SEOSPIDER_PROCESS_RUNNING + '</p>' +
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
				$('#seospider_process').modal(modalOptions);
				
				// Async event progress showed and styling
				$('#seospider_process').on('shown.bs.modal', function(event) {
					$('#seospider_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					
					// Start AJAX GET request for sitemap generation in the cache folder
					startSitemapCaching(targetSitemapLink);
				});
				
				// Remove backdrop after removing DOM modal
				$('#seospider_process').on('hidden.bs.modal',function(jqEvent){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Redirect to MVC core cpanel, discard seospider
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
						$('#seospider_process').modal('hide');
					}, 3500);
				}
			}
		}
		
		/**
		 * The first operation is to generate and precache the requested sitemap and links
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
					data: {'seospiderjsclient' : true}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(COM_JMAP_SEOSPIDER_ERROR_STORING_FILE, textStatus);
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
				showProgress(false, 100, 'striped', COM_JMAP_SEOSPIDER_GENERATION_COMPLETE, 'progress-normal');
				
				// Parse sitemap parameters
				var sitemapParams = parseURL(targetSitemapLink).params;
				var sitemapLang = sitemapParams.lang ? '&sitemaplang=' + sitemapParams.lang : '';
				var sitemapDataset = sitemapParams.dataset ? '&sitemapdataset=' + sitemapParams.dataset : '';
				var sitemapMenuID = sitemapParams.Itemid ? '&sitemapitemid=' + sitemapParams.Itemid : '';
				
				// Redirect to MVC core
				window.location.href = 'index.php?option=com_jmap&task=seospider.display&jsclient=1' + sitemapLang + sitemapDataset + sitemapMenuID;
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'progress-bar-danger');
			});
		};
		
		/**
		 * Show progress dialog bar with informations about the ongoing started process
		 * 
		 * @access private
		 * @return Void
		 */
		var showDuplicatesDetails = function(modalTitle, modalContents, didascalyFooter, linkToSkip) {
			var contentsString = '';
			
			if(modalContents.length) {
				$.each(modalContents, function(index, value){
					if(value == linkToSkip) {
						return true;
					}
					contentsString += '<li class="seospider_duplicate"><a href="' + value + '">' + value + '</a> <label class="glyphicon glyphicon-resize-vertical"></label></li>';
				});
			}
			
			// Build modal dialog
			var detailsDialog = '<div id="details_dialog" class="panel panel-primary">' +
									'<div class="panel-heading">' +
								    	'<h3 class="panel-title">' + modalTitle + '</h3>' +
								    	'<label class="closedialog glyphicon glyphicon-remove-circle"></label>' +
								    '</div>' +
								    '<div class="panel-body">' +
								    	'<ul class="seospider_duplicate">' + contentsString + '</ul>' +
								    '</div>' +
								    '<div class="panel-footer">' + 
								    	didascalyFooter + 
								    	'<div>' + COM_JMAP_SEOSPIDER_SELECTED_LINK_DETAILS + 
								    		'<a class="seospider_duplicate" href="' + linkToSkip + '">' + linkToSkip + '</a>' +
								    	'</div>' +
								    '</div>' +
								 '</div>';
			// Inject elements into content body
			$('body').append(detailsDialog);
			
			// Bind the draggable feature
			$('#details_dialog').draggable({ 
				handle: 'div.panel-heading'
			});
		}

		/**
		 * Process the asyncronous analysis of links showed in the SeoSpider list
		 * It performs parallel async requests for each link evaluating the HTTP status code in response and acting accordingly
		 *
		 * @access private
		 * @return Void
		 */
		var startLinksCrawling = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link]');
			var successIcon = ' src="' + jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-tick.png"/>';
			var failureIcon = ' src="' + jmap_baseURI + 'administrator/components/com_jmap/images/publish_x.png"/>';
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				var targetCrawledLink = $('a[data-role="link"]').get(index);
				var targetStatus = $('div[data-bind="{status}"]').get(index);
				var targetTitle = $('div[data-bind="{title}"]').get(index);
				var targetDesc = $('div[data-bind="{desc}"]').get(index);
				var targetH1 = $('div[data-bind="{h1}"]').get(index);
				var targetH2 = $('div[data-bind="{h2}"]').get(index);
				var targetH3 = $('div[data-bind="{h3}"]').get(index);
				var targetCanonical = $('div[data-bind="{canonical}"]').get(index);
				
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
					// STEP 1 - Status validation and reporting
					if(status == 200) {
						$(targetStatus).html('<span class="badge badge-success seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_LINKVALID + '">' + status + '</span>');
					} else {
						$(targetStatus).html('<span class="badge badge-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_LINK_NOVALID + '">' + status + '</span>');
						$(targetTitle).html('-');
						$(targetDesc).html('-');
						$(targetH1).html('-');
						$(targetH2).html('-');
						$(targetH3).html('-');
						$(targetCanonical).html('-');
						return;
					}
					
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());

					// STEP 2 - Title retrieval and reporting
					var title = responseDataWrappedSet.filter('title').text().trim() || '-';
					var titleBadge = '';
					// Manage title validity
					if(title && title != '-') {
						switch(true) {
							case (title.length < 40):
								titleBadge = '<div class="badge badge-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_TOOSHORT_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_TOOSHORT + '</div>';
								break;
							
							case (title.length > 80):
								titleBadge = '<div class="badge badge-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_TOOLONG_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_TOOLONG + '</div>';
								break;
						}
					} else {
						titleBadge = '<div class="badge badge-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_MISSING + '</div>';
					}
					$(targetTitle).html(titleBadge + '<div class="seospider_textlabel">' + title + '</div>');
					linksToAnalyze[index]['seospider_title'] = title;
					
					// STEP 3 - Description retrieval and reporting
					var description = responseDataWrappedSet.filter('meta[name=description]').attr('content') || '';
					var descriptionBadge = '';
					description = description.trim();
					description = description || '-';
					// Manage description validity
					if(description && description != '-') {
						switch(true) {
							case (description.length < 130):
								descriptionBadge = '<div class="badge badge-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT + '</div>';
								break;
							
							case (description.length > 180):
								descriptionBadge = '<div class="badge badge-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG + '</div>';
								break;
						}
					} else {
						descriptionBadge = '<div class="badge badge-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING + '</div>';
					}
					$(targetDesc).html(descriptionBadge + '<div class="seospider_textlabel">' + description + '</div>');
					linksToAnalyze[index]['seospider_description'] = description;
					
					// STEP 4 - Headers retrieval and reporting
					var H1Array = new Array();
					$.each(responseDataWrappedSet.find('h1'), function (index, headerTag) {
						H1Array[index] = $(headerTag).text();
					});
					var H1 = H1Array.join(' | ') || '-';
					
					var H2Array = new Array();
					$.each(responseDataWrappedSet.find('h2'), function (index, headerTag) {
						H2Array[index] = $(headerTag).text();
					});
					var H2 = H2Array.join(' | ') || '-';
					
					var H3Array = new Array();
					$.each(responseDataWrappedSet.find('h3'), function (index, headerTag) {
						H3Array[index] = $(headerTag).text();
					});
					var H3 = H3Array.join(' | ') || '-';
					
					$(targetH1).html(H1);
					$(targetH2).html(H2);
					$(targetH3).html(H3);
					
					// Report missing H1 and H2 tags
					if(!H1Array.length && !H2Array.length) {
						var noticeHeadersMissing = '<div class="badge badge-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_HEADERS_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_HEADERS_MISSING + '</div>';
						$(targetH1).html(noticeHeadersMissing);
						$(targetH2).html(noticeHeadersMissing);
					}
					
					// STEP 5 - Canonical retrieval and reporting
					var canonical = responseDataWrappedSet.filter('link[rel=canonical]').attr('href') || '-';
					$(targetCanonical).html(canonical);
					
					// STEP 6 - Count duplicated titles
					if(title && title != '-') {
						// Initialize as Array if not defined
						if(typeof(titlesCollection[generateHash(title)]) === 'undefined'){
							titlesCollection[generateHash(title)] = new Array();
						}
						titlesCollection[generateHash(title)].push(link);
					}
					
					// STEP 7 - Count duplicated descriptions
					if(description && description != '-') {
						// Initialize as Array if not defined
						if(typeof(descriptionsCollection[generateHash(description)]) === 'undefined'){
							descriptionsCollection[generateHash(description)] = new Array();
						}
						descriptionsCollection[generateHash(description)].push(link);
					}
					
					// STEP 8 - Check if the noindex directive is in place
					var indexingDirective = responseDataWrappedSet.filter('meta[name=robots]').attr('content') || '';
					if(indexingDirective) {
						var isNoIndex = indexingDirective.indexOf('noindex') >= 0;
						if(isNoIndex) {
							$(targetCrawledLink).before('<div class="badge badge-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_NOINDEX_DESC + '">' + COM_JMAP_SEOSPIDER_NOINDEX + '</div>');
						}
					}
				}).always(function(){
					// Refresh tooltips
					$('*.seospider.hasTooltip').tooltip({trigger:'hover', placement:'top'});
				});
			});
			
			// When all promises are resolved start the async duplicated title/desc count
			$.when.apply($, promisesCollection).then(function() {
				// Start analysis for each link
				$.each(linksToAnalyze, function(index, link){
					// Find the target elements
					var targetTitleDuplicates = $('div[data-bind="{title-duplicates}"]').get(index);
					var targetDescDuplicates = $('div[data-bind="{desc-duplicates}"]').get(index);
					
					// Calculate duplicates, 0 or -1 AKA no duplicates, > 0 AKA at least 1 duplicate
					if(link['seospider_title']) {
						var thisTitleHash = generateHash(link['seospider_title']);
						
						var titlesDuplicates = 0;
						if(typeof(titlesCollection[thisTitleHash]) !== 'undefined') {
							titlesDuplicates = parseInt(titlesCollection[thisTitleHash].length) - 1;
						}
						
						titlesDuplicates = titlesDuplicates > 0 ? titlesDuplicates : 0;
						
						// Find the correct badge class
						var badgeTitleClass = titlesDuplicates > 0 ? 'badge-danger' : 'badge-success';
						var badgeDetails = titlesDuplicates > 0 ? COM_JMAP_SEOSPIDER_OPEN_DETAILS : '';
						
						// Assign badge
						$(targetTitleDuplicates).html('<span class="badge ' + badgeTitleClass + ' seospider-duplicates hasTooltip" title="' + badgeDetails + '">' + titlesDuplicates + '</span>');
						
						// Disable and exclude no duplicates badge
						if(!titlesDuplicates) {
							$(targetTitleDuplicates).addClass('noduplicates');
						}
					} else {
						// Fallback
						$(targetTitleDuplicates).html('-').addClass('noduplicates');
					}
					$(targetTitleDuplicates).attr('data-link', link);
					$(targetTitleDuplicates).attr('data-titlehash', thisTitleHash);
					
					if(link['seospider_description']) {
						var thisDescriptionHash = generateHash(link['seospider_description']);
						
						var descriptionsDuplicates = 0;
						if(typeof(descriptionsCollection[thisDescriptionHash]) !== 'undefined') {
							descriptionsDuplicates = parseInt(descriptionsCollection[thisDescriptionHash].length) - 1;
						}
						
						descriptionsDuplicates = descriptionsDuplicates > 0 ? descriptionsDuplicates : 0;
						
						// Find the correct badge class
						var badgeDescriptionClass = descriptionsDuplicates > 0 ? 'badge-danger' : 'badge-success';
						var badgeDetails = descriptionsDuplicates > 0 ? COM_JMAP_SEOSPIDER_OPEN_DETAILS : '';
						
						// Assign badge
						$(targetDescDuplicates).html('<span class="badge ' + badgeDescriptionClass + ' seospider-duplicates hasTooltip" title="' + badgeDetails + '">' + descriptionsDuplicates + '</span>');
						
						// Disable and exclude no duplicates badge
						if(!descriptionsDuplicates) {
							$(targetDescDuplicates).addClass('noduplicates');
						}
					} else {
						// Fallback
						$(targetDescDuplicates).html('-').addClass('noduplicates');
					}
					
					// Assign data hash
					$(targetDescDuplicates).attr('data-link', link);
					$(targetDescDuplicates).attr('data-descriptionhash', thisDescriptionHash);
				});
				
				// Refresh tooltips
				$('*.seospider-duplicates.hasTooltip').tooltip({trigger:'hover', placement:'top'});
				
				var seospiderTable = $('table.seospiderlist').clone();
				$(seospiderTable).find('*.badge-success').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#3c763d'});
				$(seospiderTable).find('*.badge-danger').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#d9534f'});
				$(seospiderTable).find('*.badge-warning').append(' - ').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#f89406'});
				$(seospiderTable).find('div[data-bind]').filter(function(index){
					return $(this).text() === '-';
				}).text(' ');
				$(seospiderTable).find('br').remove();

				var seospiderTableHtml = seospiderTable.html();
				seospiderTableHtml = seospiderTableHtml.replace(/<a/g, '<div');
				seospiderTableHtml = seospiderTableHtml.replace(/<\/a>/g, '</div>');

				// Create a unique file name for download
				var saveDate = new Date();
				var saveDateYear = saveDate.getFullYear();
				
				var saveDateMonth = parseInt(saveDate.getMonth()) + 1;
				saveDateMonth = saveDateMonth < 10 ? '0' + saveDateMonth : saveDateMonth;
				
				var saveDateDay = saveDate.getDate();
				saveDateDay = saveDateDay < 10 ? '0' + saveDateDay : saveDateDay;
				
				var saveDateHour = saveDate.getHours();
				saveDateHour = saveDateHour < 10 ? '0' + saveDateHour : saveDateHour;
				
				var saveDateMinute = saveDate.getMinutes();
				saveDateMinute = saveDateMinute < 10 ? '0' + saveDateMinute : saveDateMinute;
				
				var saveDateSecond = saveDate.getSeconds();
				saveDateSecond = saveDateSecond < 10 ? '0' + saveDateSecond : saveDateSecond;
				
				var filename = 'seospider_report_' + 
							    saveDateYear + '-' +
							    saveDateMonth + '-' +
							    saveDateDay + '_' +
							    saveDateHour + ':' +
							    saveDateMinute + ':' +
							    saveDateSecond + '.xls';

				$('#toolbar-download button').remove();
				$('#toolbar-download').append('<a class="btn btn-small"><span class="icon-download"></span>' + COM_JMAP_EXPORT_XLS + '</a>');
				$('#toolbar-download > a').attr('href', 'data:text/html;charset=utf-8,' + encodeURIComponent('<table>' + seospiderTableHtml + '</table>'))
										  .attr('download', filename);
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
			// Add UI events
			addListeners.call(this, true);
			
			/// Execute analysis only if the view Seospider is executed
			if($('table.seospiderlist').length) {
				// Start to analyze the validation status if enabled the async mode
				startLinksCrawling();
			}
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapSeoSpider = new SeoSpider();
	});
})(jQuery);