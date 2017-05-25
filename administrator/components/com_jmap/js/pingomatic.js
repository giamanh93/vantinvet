/**
 * Pingomatic form links JS client with asyncronous features form submit
 * 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap 
 * @subpackage js 
 * @author Joomla! Extensions Store
 * @copyright (C)2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
(function($) {
	var PingomaticClient = function (formElem, configOptions) {
		/**
		 * Target Form HTMLFormElement
		 * 
		 * @access private
		 * @var Object
		 */
		var targetForm = formElem;
		
		/**
		 * Default plugin options
		 * 
		 * @access private
		 * @var Object
		 */
		var defaultOptions = {};

		/**
		 * Plugin options
		 * 
		 * @access private
		 * @var Object
		 */
		var pluginOptions;
	 
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		function openPingomaticProgress() {
			// Show first progress
			var firstProgress = '<div class="progress progress-striped active">' +
									'<div id="progressBar1" class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<span class="sr-only"></span>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSPINGOMATICTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + firstProgress + '</p>' +
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
					backdrop:'static'
				};
			$('#progressModal1').on('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append(COM_JMAP_PROGRESSPINGOMATICSUBTITLE);
				
				// Start iframe load post form
				pingomaticAjaxSubmit();
			});
			
			$('#progressModal1').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#progressModal1').on('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
			});
		};
		
		/**
		 * Open second operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		function openModelProgress() {
			// Show second progress
			var secondProgress = '<div class="progress progress-striped active">' +
									'<div id="progressBar2" class="progress-bar" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<span class="sr-only"></span>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="modal fade" id="progressModal2" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSMODELTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + secondProgress + '</p>' +
								        		'<p id="progressInfo2"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			
			var modalOptions = {
					backdrop:false
				};
			// Set 33% for progress
			$('#progressModal2').on('shown.bs.modal', function(event) {
				$('#progressModal2 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar2').css({'width':'33%'});
				// Inform user process initializing
				$('#progressInfo2').append(COM_JMAP_PROGRESSMODELSUBTITLE);
				setTimeout(function(){
					var modelAjaxSubmitCallback = function(operationResult){
						if(operationResult) {
							// Set 100% for progress
							$('#progressBar2').css({'width':'100%'});
							// Append exit message
							$('#progressInfo2').append(COM_JMAP_PROGRESSMODELSUBTITLE2SUCCESS);
							setTimeout(function(){
								// Remove all
								$('#progressModal1').modal('hide');
								$('#progressModal2').modal('hide');
							}, 2000);
						} else {
							// Set 100% for progress
							$('#progressBar2').css({'width':'100%'}).addClass('progress-bar-danger');
							// Append exit message
							$('#progressInfo2').append('<p>' + COM_JMAP_PROGRESSMODELSUBTITLE2ERROR + '</p>');
							setTimeout(function(){
								// Remove all
								$('#progressBar2').removeClass('progress-bar-danger');
								$('#progressModal1').modal('hide');
								$('#progressModal2').modal('hide');
							}, 3000);
						}
					};
					modelAjaxSubmit(modelAjaxSubmitCallback);
				}, 500);
			});

			$('#progressModal2').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#progressModal2').on('hidden.bs.modal',function(){
				$(this).remove();
			});
		};
		
		/**
		 * Make simulated ajax submit form to Pingomatic with hidden iframe
		 * 
		 * @access private
		 * @return Boolean
		 */
		function pingomaticAjaxSubmit() {
			// Set dinamically task target in form to ajaxserver.display
			$(targetForm).attr('target', 'pingomatic_iframe');
			$(targetForm).attr('action', jmap_urischeme + '://pingomatic.com/ping/');
			
			// No exception handling, so always return true
			return formSubmitWithCallback($(targetForm), $('#pingomatic_iframe'), function() {
				// Reset resources data on form
				$(targetForm).removeAttr('target');
				$(targetForm).attr('action', 'index.php');
				// Set 100% for progress
				$('#progressBar1').css({'width':'100%'});
				$('#progressInfo1').append(COM_JMAP_PROGRESSPINGOMATICSUBTITLE2SUCCESS);
				
				// Wait for 100% progress
				setTimeout(function(){
					// Rethrow to create progress
					openModelProgress();
				}, 500);
			});
		};

		/**
		 * Submit a form to an iframe and execute callback on load complete 
		 * 
		 * @access private
		 * @return Boolean
		 */
		function formSubmitWithCallback(form, frame, successFunction) {
			// Set callback
			var callback = function () {
				if(successFunction) {
					successFunction();
				}
				frame.off('load', callback);
		   };
		   
		   // Bind callback to iframe load event 
		   frame.on('load', callback);
		   
		   // Trigger form submit to iframe
		   form.trigger('submit');
		   
		   // No exception handling, so always return true
		   return true;
		}
		
		/**
		 * Switch ajax submit form to model business logic
		 * 
		 * @access private
		 * @param Object callback
		 * @return Void
		 */
		function modelAjaxSubmit(callback) {
			// Increment pre HTTP request
			$('#progressBar2').css({'width':'66%'});
			// Final status for model operation
			var success = false;
			// Reset resources data on form for security safe async not avoidable
			$(targetForm).removeAttr('target');
			$(targetForm).attr('action', 'index.php');
			
			// Prepare form to submit via ajax
			$(targetForm).ajaxForm();
			
			// Extra object to send to server
			var ajaxParams = { 
					idtask : 'storeUpdatePingomatic',
					template : 'json',
					param: $('input[name=id]', targetForm).val()
			     };
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxParams); 

			// Setup initial options object for real ajaxSubmit JSON2JSON
			var ajaxSubmitOptions = {
					type: 'POST',
					dataType: 'json',
					async: true,
					data: {data: uniqueParam},
					beforeSerialize: function(formData, formObject, options){
						// Set dinamically task target in form to ajaxserver.display
						$('input[name=task]', targetForm).val('ajaxserver.display');
						
						//Add dinamically document format to form submitted
						$(targetForm).append('<input type="hidden" name="format" value="json"/>');
					},
					success: function(data, textStatus, jqXHR){
						var data;
						// Set result value
						success = data.result;
						
						// If errors found inside model working
						if(!success && data.errorMsg) {
							$('#progressInfo2').append( data.errorMsg + ' - ');
						}
						// Reset dinamically task target in form to null empty value
						jqXHR.always(function(){
							$('input[name=task]', targetForm).val('');
							// Append last ping time from server table
							$('#lastping', targetForm).html(data.lastping);
							// Check if new pinged stored records and update hidden field to avoid duplicates
							if(!$('input[name=id]').val()) {
								$('input[name=id]').val(data.id);
							}
						});

						callback(success);
					},
					error: function(jqXHR, textStatus, error){
						// Append error details
						$('#progressInfo2').append(error.message + ' - ');
						// Reset dinamically task target in form to null empty value
						jqXHR.always(function(){
							$('input[name=task]', targetForm).val('');
						});
						
						callback(success);
					}
			};
			// Prepare form to submit via ajax
			$(targetForm).ajaxSubmit(ajaxSubmitOptions);
		};
		
		/**
		 * Public interface to submit ajax form to Pingomatic web service
		 * 
		 * @access public
		 * @return Void
		 */
		this.submitFormPingomatic = function() {
			// Start first progress appending
			openPingomaticProgress();
		};
		
		/**
		 * Init function dummy constructor
		 * @access private
		 * @method IIFE
		 * @return Void
		 */
		(function init() {
			// Init options
			pluginOptions = $.extend({}, defaultOptions, configOptions );
		}).call(this);
	};
	
	/**
	 * jQuery Pingomatic plugin
	 */
	$.fn.Pingomatic = function(options) {
		// Cycle on pre initialized wrapped set
		this.each(function(index, formElem){
			// Do form element validation
			if(!$(formElem).validate()) {
				return false;
			}	
			
			// Mutex radio button, at least 1 enabled
			var enabledServices = $('input[name^=chk_][value=1]', formElem);
			var countEnabledServices = 0;
			$.each(enabledServices, function(index, elem){
				if($(elem).prop('checked')) {
					countEnabledServices++;
				}
			});
			if(!countEnabledServices) {
				var requiredHtmlChunk = '<label class="validation services label label-danger">' + COM_JMAP_SELECTONESERVICE + '</label>';
				$('div.panel-success:last-child').after(requiredHtmlChunk);
				// Register to disable error label
				$('label.radio[for^=chk_]').on('click', function(){
					$('label.validation.services').remove();
				});
				return false;
			}
			
			// Instance of Pingomatic manager
			var client = new PingomaticClient(formElem, options);
			
			// Call submit sending ajax form to Pingomatic
			client.submitFormPingomatic();
		});
	};
})(jQuery);

// Popover configuration
jQuery(function($){
	var iFrameContext = 'div.popover-content';
	/**
	 * Enables bootstrap popover
	 */
	$('label.hasClickPopover').popover({
		trigger:'click', 
		html:1,
		placement: 'bottom',
		content: '<iframe id="sitemap_iframe" src="' + jmap_baseURI + 'index.php?option=com_jmap&view=sitemap&tmpl=component' + '"></iframe>'
	}).on('shown.bs.popover', function(){
		$('div.popover-content').css('padding', 0);
		$('iframe', iFrameContext).css({'border':'none', 'height':'300px'});
		// Get div popover container width to center waiter
		var containerWidth = $('div.popover-content').width() / 2;
		$('div.popover-content').prepend('<div/>').children('div').text(COM_JMAP_LOADING).css({
            'position': 'absolute',
            'font-size': '12px',
            'margin': '75px ' + parseInt(containerWidth - 50) + 'px'
        });
		$('div.popover-content').prepend('<img/>').children('img').attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif').css({
            'position': 'absolute',
            'margin': '50px ' + parseInt(containerWidth - 12) + 'px',
            'width': '24px'
        });
		
		$('#sitemap_iframe').on('load', function(){
			/*var iframeHead = $(this).contents().find('head');
			iframeHead.append('<script src="' + jmap_baseURI + 'administrator/components/com_jmap/js/urlpicker.js"></script>');*/
			var doc = this.contentDocument ? this.contentDocument : (this.contentWindow.document || this.document);
			var script = doc.createElement("script");
			script.type = "text/javascript";
			script.src = jmap_baseURI + 'administrator/components/com_jmap/js/urlpicker.js';
			doc.body.appendChild(script);
			$('div.popover-content img, div.popover-content div').remove();
		});
	});
	
	$(document).on('click', 'h3.popover-title', function(){
		$('label.hasClickPopover').popover('toggle');
	});
	
	// Add class to broadcast button
	$('.icon-broadcast').parent().addClass('btn-primary');
});