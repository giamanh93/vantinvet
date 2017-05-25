/**
* Manage client tasks inside component CPanel 
* 
* @package JMAP::CPANEL::administrator::components::com_jmap 
* @subpackage js 
* @author Joomla! Extensions Store
* @copyright (C)2015 Joomla! Extensions Store
* @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
jQuery(function($){
	var CPanel = $.newClass ({
		/**
		 * Main selector
		 * 
		 * @access public
		 * @property prototype
		 * @var array
		 */
		selector : null,
		
		/**
		 * Target selector
		 * 
		 * @access public
		 * @property prototype
		 * @var array
		 */
		targetSelector : null, 
		
		/**
		 * Canvas context
		 * 
		 * @access public
		 * @param String
		 */
		canvasContext : null,
		
		/**
		 * Chart data to render, copy from global injected scope
		 * 
		 * @access private
		 * @var Object
		 */
    	chartData : {},
    	
    	/**
		 * Status of the model save entity process for the robots entries
		 * 
		 * @access private
		 * @var Object
		 */
    	modelSaveEntitySuccess : false,
    	
    	/**
		 * Charts options
		 * 
		 * @access private
		 * @var Object
		 */
    	chartOptions : {animation:true, scaleFontSize: 11, scaleOverride: true, scaleSteps:1, scaleStepWidth: 50},

    	/**
		 * First progress snippet
		 * 
		 * @access private
		 * @var String
		 */
		firstProgress : '<div class="progress progress-striped active">' +
							'<div id="progressBar1" class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
								'<span class="sr-only"></span>' +
							'</div>' +
						'</div>',
    	
    	/**
    	 * Update status selector
    	 * 
    	 * @access private
    	 * @var String
    	 */
    	updateStatusSelector : '#updatestatus label.label-danger',
    	
    	/**
    	 * Update process button snippet with placeholder to trigger the update process
    	 * 
    	 * @access private
    	 * @var String
    	 */
    	updateButtonSnippet : '<button id="updatebtn" data-content="' + COM_JMAP_EXPIREON + '%EXPIREON%" class="btn btn-small btn-primary">' + 
    							'<span class="icon-upload"></span>' + COM_JMAP_CLICKTOUPDATE + 
    						  '</button>',

		/**
		 * Object initializer
		 * 
		 * @access public
		 * @param string selector 
		 */
		init : function(selector, targetSelector) {
			this.constructor.prototype.selector = selector;
			this.constructor.prototype.targetSelector = targetSelector;
			
			//Registrazione eventi
			this.registerEvents();
			
			// Get target canvas context 2d to render chart
        	if(!!document.createElement('canvas').getContext && $('#chart_canvas').length) {
        		this.constructor.prototype.canvasContext = $('#chart_canvas').get(0).getContext('2d');
        		
        		$(window).on('resize', {bind:this}, function(event){
        			event.data.bind.resizeRepaintCanvas();
            	})
            	
            	// Start generation
            	this.resizeRepaintCanvas(true);
        	}
        	
        	// Trigger the updates license status checker
        	var context = this;
        	setTimeout(function(){
        		context.checkUpdatesLicenseStatus();
        	}, 500);
		},
	
		/**
		 * Register events for user interaction
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		registerEvents : function() {
			var context = this;
			
			// Register events select articoli
			$(this.selector).on('change', {bind:this}, function(event) {
				// Disabled complementary dropdown
				switch(event.target.id) {
					case 'menu_datasource_filters':
						event.target.value ? $('#datasets_filters').attr('disabled', true) : $('#datasets_filters').attr('disabled', false);
						break;
						
					case 'datasets_filters':
						event.target.value ? $('#menu_datasource_filters').attr('disabled', true) : $('#menu_datasource_filters').attr('disabled', false);
						break;
						
					case 'language_option':
						var langValue = $(event.target).val().replace('-', '_');
						$('#jmap_langflag').remove();
						$(event.target).parent().append('<img id="jmap_langflag" src="' + jmap_baseURI + 'media/mod_languages/images/' + langValue + '.gif" alt="flag"/>');
						break;
				}
				
				event.data.bind.refreshCtrls(event.target, event.target.value); 
			});
			// Trigger change by default on page load to populate language query string at startup
			$('#language_option').trigger('change');
			
			// Trigger if multilanguage is off and random links are on
			if(!$('#language_option').length && jmap_linksRandom) {
				this.refreshCtrls();
			}
			
			// Trigger if multilanguage is off and force format for links is on
			if(!$('#language_option').length && jmap_forceFormat) {
				this.refreshCtrls();
			}
			
			// Enables bootstrap popover
			$('label.hasClickPopover').popover({
				trigger: 'click', 
				placement: 'left', 
				html: 1,
				noTitle: true
			}).on('shown.bs.popover', function(){
				$(context.selector).trigger('change', [true]);
			});

			$('input.hasClickPopover').popover({
				trigger: 'click', 
				placement: 'top', 
				html: 1,
				noTitle: false,
				title: function() {
					return COM_JMAP_CRONJOB_GENERATED_SITEMAP_FILE;
				},
				content: function() {
					var queryString = $(this).val();
					 // Split into key/value pairs
				    var queries = queryString.split("&");
				    var params = {};

				    // Convert the array of strings into an object
				    for ( i = 0, l = queries.length; i < l; i++ ) {
				        temp = queries[i].split('=');
				        params[temp[0]] = temp[1];
				    }
				    
				    // Build the file name
				    var sitemapFrontendFilename = jmap_splittingStatus && params.format != 'videos' ? 'sitemapindex_' : 'sitemap_';
			    	sitemapFrontendFilename += params.format;
			    	
				    if(params.hasOwnProperty('lang')) {
				    	sitemapFrontendFilename += '_' + params.lang;
				    }
				    if(params.hasOwnProperty('dataset')) {
				    	sitemapFrontendFilename += '_dataset' + params.dataset;
				    }
				    if(params.hasOwnProperty('Itemid')) {
				    	sitemapFrontendFilename += '_menuid' + params.Itemid;
				    }
				    sitemapFrontendFilename += '.xml';
				    
					var concatenatePingXmlFormat = 	"<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'>" + COM_JMAP_PING_GOOGLE + "</a>" +
		 											"<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'>" + COM_JMAP_PING_BING + "</a>" +
 													"<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'>" + COM_JMAP_PING_YANDEX + "</a>" +
													"<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'>" + COM_JMAP_PING_BAIDU + "</a>";
				    
					return '<input type="text" class="popover-content" value="' + jmap_livesite + '/' + sitemapFrontendFilename + '"/>' +
						   '<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="' + COM_JMAP_PING_SITEMAP_CRONJOB + '" data-content="' + concatenatePingXmlFormat + '"></label>' +
						   '<label class="glyphicon glyphicon-pencil hasTooltip" title="' + COM_JMAP_ROBOTS_SITEMAP_ENTRY_CRONJOB + '" data-role="saveentity"></label>';
				}
			}).on('shown.bs.popover', function(event){
				$('#xmlsitemap_export label.hasTooltip').tooltip({trigger:'hover', placement:'top'});
				
				// Enables bootstrap popover
				$('#xmlsitemap_export label.hasClickPopover').popover({
					trigger: 'click', 
					placement: 'left', 
					html: 1,
					container: '#xmlsitemap_export',
					noTitle: true
				});
			});

			// Ensure closing it when click on other DOM elements
			$(document).on('click', 'body', function(jqEvent){
				if(!$(jqEvent.target).hasClass('hasClickPopover') && !$(jqEvent.target).hasClass('popover-content')) {
					$('label.hasClickPopover, input.hasClickPopover, li.hasClickPopover').popover('hide');
				}
			});
			
			// First Fancybox content type for XML sitemaps format generation/export
			if($('a.fancybox').length) {
				$("a.fancybox").fancybox();
			}
			
			// Google maps Fancybox loading and instance
			if($('a.fancybox[data-role="opengmap"]').length) {
				$('a.fancybox[data-role="opengmap"]').fancybox({
					beforeLoad: function() {
						$('#gmap').show();
						map = new GMaps({
					        div: '#gmap',
					        lat: 40.730610,
					        lng: -73.935242,
					        zoom: 1
					      });
						
						GMaps.geocode({
							  address: jmap_geositemapAddress,
							  callback: function(results, status) {
							    if (status == 'OK') {
							      var latlng = results[0].geometry.location;
							      map.setCenter(latlng.lat(), latlng.lng());
							      map.addMarker({
							        lat: latlng.lat(),
							        lng: latlng.lng()
							      });
							      map.setZoom(10);
							    }
							  }
							});
					},
					afterClose : function () {
						$('#gmap').remove();
						$('a[data-role=opengmap]').after('<div id="gmap"></div>');
					},
					title : jmap_geositemapAddress
				});
			}
			
			if($('a.fancybox.rss').length) {
				$("a.fancybox.rss").fancybox({
						minWidth: '680'
					});
			}
			
			if($('a.fancybox_iframe').length) {
				$("a.fancybox_iframe").fancybox({
					type:'iframe',
					minWidth: '800',
					maxWidth: '800',
					minHeight: '640',
					maxHeight: '640',
					afterLoad:function(upcoming){
							$($('iframe[id^=fancybox]')).attr('scrolling','no');
						} 
				});
			}
			
			$('#fancy_closer').on('click', function(){
				parent.jQuery.fancybox.close();
				return false;
			});
			
			$('label.hasRobotsPopover').popover({trigger:'hover', placement:'bottom'});
			
			// Pinger window open to win on iframe crossorigin limitations
			$(document).on('click', 'a.pinger', function(jqEvent){
				// Prevent open link
				jqEvent.preventDefault();
				
				// Simple window post to the search engine for HTML submission
				if($(this).data('type') != 'rpc') {
					var thisLinkToPing = $(this).attr('href');
					window.open(thisLinkToPing, 'pingwindow', 'width=800,height=480');
				} else {
					context.rpcSitemapPing(this);
				}
				return false;
			});
			
			// Label to manage saveEntity on sitemap model
			$(document).on('click', 'label[data-role=saveentity]', function() {
				// Trigger JS app processing to create root sitemap file
				var ajaxTargetLink = $(this).prevAll('input').val();
				// Start model ajax saveEntity
				context.openSaveEntityProgress(ajaxTargetLink);
			});
			
			// Robots add entries
			$('#robots_adder').on('click', {bind:this}, function(jqEvent){
				jqEvent.preventDefault();
				
				// Call the adder callback
				jqEvent.data.bind.addRobotsEntry(); 
			});
			
			// Component updater ignition start
			$(document).on('click', '#updatebtn', function(jqEvent){
				context.performComponentUpdate();
			});
		},
	
		/**
		 * Refresh input link types and a types inside lightbox
		 * 
		 * @access public
		 * @method prototype
		 * @param String value the language value selected
		 * @return void 
		 */
		refreshCtrls : function(elem, value) {
			// Controls->param mapping intelligent append/replace
			var controlParamMapper = {'language_option':'&lang=',
									  'menu_datasource_filters':'&Itemid=',
									  'datasets_filters':'&dataset='
									 };
			var mappedQueryStringParam = controlParamMapper[$(elem).attr('id')]; 
			
			// Inject default option
			$(this.targetSelector).each(function(index, item) {
				switch($(item).prop('tagName').toLowerCase()) {
					case 'a':
						var appendValue = '';
						// If chosen valid language
						if(value) {
							if($(item).attr('data-role') == 'pinger') {
								appendValue = encodeURIComponent(mappedQueryStringParam + value);
							} else {
								appendValue = mappedQueryStringParam + value;
							}
						}
						
						var currentValue = $(item).attr('href');
						// Existing param
						if(currentValue.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
							if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {} else {
								currentValue = currentValue.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue);
							}
						} else {
							// Case new param appended
							currentValue = currentValue + appendValue;
						}
						
						// Resetting value
						$(item).attr('href', currentValue);
					break;
					
					case 'input':
					default: 
						var appendValue = '';
						// If chosen valid language
						if(value) {
							if($(item).attr('data-role') == 'pinger') {
								appendValue = encodeURIComponent(mappedQueryStringParam + value);
							} else {
								appendValue = mappedQueryStringParam + value;
							}
						}
						var currentValue = $(item).val();
						
						// If auto versioning reset version parameter
						if(jmap_linksRandom) {
							currentValue = currentValue.replace(new RegExp(".ver=\\d+", "gi"), '');
						}
						
						// If auto append extra format query string parameter reset it
						if(jmap_forceFormat && $(item).attr('data-role') == 'sitemap_links_sef' && !$(item).attr('data-html')) {
							currentValue = currentValue.replace(new RegExp(".format=.+", "gi"), '');
						}
						
						// Manage double mode for no-SEF or SEF links
						if($(item).attr('data-role') == 'sitemap_links_sef') {
							switch($(elem).attr('id')) {
								case 'language_option':
									var regexString = typeof(jmap_sef_alias_links) !== 'undefined' ? jmap_sef_alias_links : 'component';
									// Existing param
									if(currentValue.match(new RegExp("http.*/.{2}/" + regexString + "|http.*/.{2}-.{2}/" + regexString, "i"))) {
										if($(item).data('language') != 1) {
											currentValue = currentValue.replace(new RegExp(".{2}/" + regexString + "|.{2}-.{2}/" + regexString, "i"), value + '/' + regexString);
										}
									} else {
										// Case new param appended
										currentValue = currentValue.replace(new RegExp(regexString, "i"), value + '/' + regexString);
									}
									break;
									
								case 'menu_datasource_filters':
										// Existing param
										if(currentValue.match(new RegExp("Itemid", "gi"))) {
											if(value) {
												currentValue = currentValue.replace(new RegExp("Itemid=\\d+", "gi"), 'Itemid=' + value);
											} else {
												currentValue = currentValue.replace(new RegExp("\\?Itemid=\\d+", "gi"), '');
											}
										} else {
											// Case new param appended
											if(value) {
												currentValue = currentValue + '?Itemid=' + value;
											}
										}
									break;
								case 'datasets_filters':
									// Existing param
									if(currentValue.match(new RegExp("dataset", "gi"))) {
										if(value) {
											currentValue = currentValue.replace(new RegExp("\\d+-dataset", "gi"), value + '-dataset');
										} else {
											currentValue = currentValue.replace(new RegExp("/\\d-formatted/\\d+-dataset", "gi"), '');
										}
									} else {
										// Case new param appended
										if(value) {
											currentValue = currentValue + '/0-formatted/' + value + '-dataset';
										}
									}
								break;
							}
							var currentDataValueNoSef = $(item).attr('data-valuenosef');
							// Existing param
							if(currentDataValueNoSef.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
								$(item).attr('data-valuenosef', currentDataValueNoSef.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue));
							} else {
								// Case new param appended
								$(item).attr('data-valuenosef', currentDataValueNoSef + appendValue);
							}
						} else {
							// Existing param
							if(currentValue.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
								if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {} else {
									currentValue = currentValue.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue);
								}
							} else {
								// Case new param appended
								currentValue = currentValue + appendValue;
							}
						}
						
						// Auto append extra query string param for sitemap versioning AKA force GWT cache to refresh 
						if(jmap_linksRandom) {
							// Already a query string?
							if(currentValue.match(new RegExp("\\?", "gi"))) {
								currentValue += '&ver=' + Math.floor((Math.random() * 10000) + 1);
							} else {
								// New query string append
								currentValue += '?ver=' + Math.floor((Math.random() * 10000) + 1);
							}
						}
						
						// Auto append extra format query string parameter
						if(jmap_forceFormat && $(item).attr('data-role') == 'sitemap_links_sef' && !$(item).attr('data-html')) {
							var linkFormat = $(item).data('valuenosef').match(/format=([a-z]+)/i);
							// Already a query string?
							if(currentValue.match(new RegExp("\\?", "gi"))) {
								currentValue += '&format=' + linkFormat[1];
							} else {
								// New query string append
								currentValue += '?format=' + linkFormat[1];
							}
						}
						
						// Resetting value
						$(item).val(currentValue);
						$(item).attr('value', currentValue);
					break;
				}
	  		}); 
		},
		
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return void 
		 */
		openSaveEntityProgress : function(ajaxLink) {
			var context = this;

			// Build modal dialog
			var modalDialog =	'<div class="modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_ROBOTSPROGRESSTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay').fadeOut();
			
			var modalOptions = {
					backdrop:'static'
				};
			$('#progressModal1').on('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLE + '</p>');
				
				setTimeout(function(){
					context.modelSaveEntity(ajaxLink).always(function(){
						if(this.modelSaveEntitySuccess) {
							// Set 100% for progress
							$('#progressBar1').css({'width':'100%'});
							// Append exit message
							$('#progressInfo1').append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLESUCCESS + '</p>');
							setTimeout(function(){
								// Remove all
								$('#progressModal1').modal('hide');
							}, 3000);
						} else {
							// Set 100% for progress
							$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-danger');
							// Append exit message
							$('#progressInfo1').append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLEERROR + '</p>');
							setTimeout(function(){
								// Remove all
								$('#progressModal1').modal('hide');
							}, 3000);
						}
					});
				}, 500);
			});
			
			$('#progressModal1').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#progressModal1').on('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay').fadeIn();
			});
		},
		
		/**
		 * Switch ajax submit form to model business logic
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return Promise
		 */
		modelSaveEntity : function(ajaxLink) {
			// Extra object to send to server
			var ajaxParams = { 
					idtask : 'robotsSitemapEntry',
					template : 'json',
					param: ajaxLink
			     };
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxParams); 

			// Request JSON2JSON
			return $.ajax({
		        type: "POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        async: true,
		        data: {data : uniqueParam } , 
		        success: function(data, textStatus, jqXHR)  {
					// Set result value
					this.modelSaveEntitySuccess = data.result;
					// If errors found inside model working
					if(!this.modelSaveEntitySuccess && data.errorMsg) {
						$('#progressInfo1').append('<p>' + data.errorMsg + '</p>');
					}
	            },
				error: function(jqXHR, textStatus, error){
					// Append error details
					$('#progressInfo1').append('<p>' + error.message + '</p>');
				}
			}); 
		},
		
		 /**
		 * Interact with ChartJS lib to generate charts
		 * 
		 * @access private
		 * @return Void
		 */
        generateLineChart : function(animation) {
        	var bind = this;
        	// Instance Chart object lib
        	var chartJS = new JMapChart(this.canvasContext);
        	
        	// Max value encountered
        	var maxValue = 9;
        	
        	// Normalize chart data to render
        	this.constructor.prototype.chartData.labels = new Array();
        	this.constructor.prototype.chartData.datasets = new Array();
        	var subDataSet = new Array();
            $.each(jmapChartData, function(label, value){
            	var labelSuffix = label.replace(/([A-Z])/g, "_$1").toUpperCase()
            	bind.constructor.prototype.chartData.labels[bind.chartData.labels.length] = eval('COM_JMAP_' + labelSuffix + '_CHART');;
            	subDataSet[subDataSet.length] = value = parseInt(value);
            	if(value > maxValue) {
            		maxValue = value;
            	}
            });
            
            // Override scale
            this.constructor.prototype.chartOptions.scaleStepWidth = 10;
            if((maxValue / 100) > 0) {
            	var multiplier = parseInt(maxValue / 100);
            	this.constructor.prototype.chartOptions.scaleStepWidth = 10 + (multiplier * 10);
            }
            this.constructor.prototype.chartOptions.scaleSteps = parseInt((maxValue / this.chartOptions.scaleStepWidth) + 1);
            
            this.constructor.prototype.chartData.datasets[0] = {
            		fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					data : subDataSet
            };
        	
            // Override options
            this.constructor.prototype.chartOptions.animation = animation;
            
            // Paint chart on canvas
        	chartJS.Line(this.chartData, this.chartOptions);
        },
        
        /**
		 * Make fluid canvas width with repaint on resize
		 * 
		 * @access private
		 * @return Void
		 */
        resizeRepaintCanvas : function(animation) {
        	// Get HTMLCanvasElement
            var canvas = $('#chart_canvas').get(0);
            // Get parent container width
            var containerWidth = $(canvas).parent().width();
            // Set dinamically canvas width
            canvas.width  = containerWidth;
            $(canvas).css('min-width', canvas.width);
            canvas.height = 170;
            // Repaint canvas contents
            this.generateLineChart(animation);
        },
        
		/**
		 * Make fluid canvas width with repaint on resize
		 * 
		 * @access private
		 * @return Void
		 */
        addRobotsEntry : function() {
        	// Reuse snippets
			var validationSnippet = '<ul class="errorlist"><li class="validation label label-danger">' + COM_JMAP_ROBOTS_REQUIRED + '</li></ul>';
			var messageSnippet =    '<div class="robots_messages alert alert-success">' +
										'<h4 class="alert-heading">Message</h4>' +
										'<p>' + COM_JMAP_ROBOTS_ENTRY_ADDED + '</p>' +
									'</div>';

        	// Retrieve values
			var robotsRule = $('#robots_rule').val();
			var robotsEntry = $('#robots_entry').val();
			
			if(robotsEntry) {
				// Append text to the text area
				$('#robots_contents').val(function(_, val){
					return val + '\n' + robotsRule + robotsEntry; 
				});
				
				// Scroll to bottom the textarea
				$("#robots_contents").scrollTop($("#robots_contents")[0].scrollHeight);
				
				// Reset value
				$('#robots_entry').val('');
				
				// Append message
				$('#system-message-container').html(messageSnippet);
				setTimeout(function(){
					$('.robots_messages').fadeOut(500, function(){
						$(this).remove();
					});
				},1000);
			} else {
				$('#robots_entry').next('ul').remove().end().after(validationSnippet);
				$('#robots_entry').addClass('error');
				
				$('#robots_entry').on('keyup', function(jqEvent){
					$(this).removeClass('error');
					$(this).next('ul').remove();
				});
			}
        },
        
		/**
		 * Perform the remote check to validate the updates status license
		 * If the license is valid the update button will be shown
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		checkUpdatesLicenseStatus : function() {
			var updateSnippet = this.updateButtonSnippet;
			var replacements = {"%EXPIREON%":""};

			// Is there an outdated status?
			if($(this.updateStatusSelector).length) {
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'getLicenseStatus',
						param: {}
				     };
				// Unique param 'data'
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Request JSON2JSON
				$.ajax({
			        type: "POST",
			        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
			        dataType: 'json',
			        context: this,
			        async: true,
			        data: {data : uniqueParam } , 
			        success: function(data, textStatus, jqXHR)  {
						// If the updates informations are successful go on, ignore every error condition
						if(data.success) {
							replacements = {"%EXPIREON%":data.expireon};
							
							updateSnippet = updateSnippet.replace(/%\w+%/g, function(all) {
								   return replacements[all] || all;
								});
							
							// Now append the update button beside the status label
							$(this.updateStatusSelector).parent().after(updateSnippet);
							
							// Apply the popover
							$('#updatebtn').popover({trigger:'hover', placement:'right'})
						}
		            }
				}); 
			}
		},
		
		/**
		 * Start the managed update process of the componenent showing 
		 * progress bar and error messages to the user
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		performComponentUpdate : function() {
			var context = this;
			
			// Build modal dialog
			var modalDialog =	'<div class="modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_UPDATEPROGRESSTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : false
				};
			$('#progressModal1').on('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'downloadComponentUpdate',
						param: {}
				     };
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Requests JSON2JSON chained
				var chained = $.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					data : {
						data : uniqueParam
					},
					dataType : "json"
				}).then(function(data) {
					$('#progressBar1').css({'width':'75%'});
					// Inform user process initializing
					$('#progressInfo1').empty().append('<p>' + COM_JMAP_INSTALLING_UPDATE_SUBTITLE + '</p>');
					
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						// Extra object to send to server
						var ajaxParams = { 
								idtask : 'installComponentUpdate',
								param: {}
						     };
						var uniqueParam = JSON.stringify(ajaxParams); 
						return $.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
							type : "POST",
							data : {
								data : uniqueParam
							},
							dataType : "json"
						});
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
						setTimeout(function(){
							// Remove all
							$('#progressModal1').modal('hide');
						}, 3000);
						
						// Stop the chained promises
						return $.Deferred().reject();
					}
				});
				 
				chained.done(function( data ) {
					// Data retrieved from url2 as provided by the first request
					if(data.result) {
						// Phase 2 OK, set 100% width and mark as completed the whole process
						$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-success');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_COMPLETED_UPDATE_SUBTITLE + '</p>');
						
						// Now refresh page
						setTimeout(function(){
							window.location.reload();
						}, 1500);
					} else {
						// Set 100% for progress
						$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
						setTimeout(function(){
							// Remove all
							$('#progressModal1').modal('hide');
						}, 3000);
					}
				});
			});
			
			$('#progressModal1').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#progressModal1').on('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
			});
		},
		
		/**
		 * Ping/submit the sitemap by AJAX using a remote XML-RPC service
		 * 
		 * @access public
		 * @property prototype
		 * @param HTMLElement element
		 * @return void 
		 */
		rpcSitemapPing : function(element) {
			// Retrieve the url of the submitting sitemap clicked
			var URIs = $(element).attr('href').split('?');
			var sitemapLink = decodeURIComponent(URIs[1]);
			
			// Build modal dialog
			var modalDialog =	'<div class="modal fade" id="pingingModal">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PINGING_SITEMAP_TOBAIDU + '</h4>' +
								        		'<label class="closepinging glyphicon glyphicon-remove-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        	'</div>' +
						        	'</div>' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay').fadeOut();
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : true
				};
			
			$('#pingingModal').on('shown.bs.modal', function(event) {
				$('#pingingModal div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBAIDU_PLEASEWAIT + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'submitSitemapToBaidu',
						param: sitemapLink
				     };
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Requests JSON2JSON chained
				$.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).then(function(data) {
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-success');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBAIDU_COMPLETE + '</p>');
						
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
					}
					
					setTimeout(function(){
						// Remove all
						$('#pingingModal').modal('hide');
					}, 3000);
				});
			});
			
			$('#pingingModal').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#pingingModal').on('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay').fadeIn();
			});
			
			// Live event binding for close button AKA stop process
			$(document).on('click', 'label.closepinging', function(jqEvent){
				$('#pingingModal').modal('hide');
			});
		}
	}); 
 
	// Start JS application
	$.cpanelTasks = new CPanel('#language_option, #menu_datasource_filters, #datasets_filters', 'input[data-role=sitemap_links], input[data-role=sitemap_links_sef], a[data-role=pinger], a[data-role=torefresh], #xmlsitemap a[href*=sitemap], #xmlsitemap_xslt a[href*=sitemap], #xmlsitemap_export a[href*=sitemap], #rssfeed a[href*=sitemap], a.jmap_analyzer, a.jmap_metainfo, a.jmap_seospider');
});