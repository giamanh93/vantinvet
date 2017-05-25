/**
 * Htaccess editor manager, manage adding directives, correct paths, local and
 * session storage for htaccess restoring
 * 
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var Htaccess = function() {
		/**
		 * Store locally the original htaccess contents to restore it later
		 * 
		 * @access private
		 * @var String
		 */
		var originalHtaccess = null;
		
		/**
		 * Store the session versioning of htaccess to serve the UNDO/REDO function
		 * 
		 * @access private
		 * @var Array
		 */
		var versioningHtaccess = new Array(); 
		
		/**
		 * Validation snippet
		 * 
		 * @access private
		 * @var Object
		 */
		var validationSnippet = '<ul class="errorlist"><li class="validation label label-danger">' + COM_JMAP_HTACCESS_REQUIRED + '</li></ul>';
		
		/**
		 * Directive adding message snippet
		 * 
		 * @access private
		 * @var Object
		 */
		var messageSnippet = '<div class="htaccess_messages alert alert-success">' +
								'<h4 class="alert-heading">Message</h4>' +
								'<p>' + COM_JMAP_HTACCESS_DIRECTIVE_ADDED + '</p>' +
							'</div>';
		
		/**
		 * Directive dropdown jQobject
		 * 
		 * @access private
		 * @var Object
		 */
		var directiveDropdown = $('#htaccess_directive');
		
		/**
		 * Validate inputs required based on directive type
		 * 
		 * @access private
		 * @return Boolean
		 */
		var validateInputs = function() {
			for(var i=0; i < arguments.length; i++) {
				var values = arguments[i];
				if(!values) {
					$('ul.errorlist').remove();
					$('div.paths').append(validationSnippet);
					$('div.paths input').filter(function() {
					    return !this.value;
					}).addClass('error');
					
					return false;
				}
			}
			
			return true;
		};

		/**
		 * Logic for the directive adder, based on the type of directive the
		 * path is safely corrected and appended to the htaccess
		 * 
		 * @access private
		 * @return Void
		 */
		var addHtaccessDirective = function() {
			// Retrieve the type of directive to add
			var directive = parseInt($('option:selected', directiveDropdown).data('directive'));
			var directiveType = $('option:selected', directiveDropdown).data('type');
			var directiveName = null;

			// Populate the array of values
			var directiveValues = new Array();
			switch (directive) {
				case 404:
					var path1 = $('#path1').val();
					
					// Validating values
					if(!validateInputs(path1)) {
						return;
					}
					
					directiveValues.push(path1);
					directiveName = 'Redirect 404 ';
					break;
	
				case 301:
					// Retrieve values
					var path1 = $('#path1').val();
					var path2 = $('#path2').val();
					
					// Validating values
					if(!validateInputs(path1, path2)) {
						return;
					}
					
					directiveValues.push(path1);
					directiveValues.push(path2);
					directiveName = 'Redirect 301 ';
					break;
			}

			// Act accordingly
			var endSlash = '';
			if(directiveType == 'folder') {
				endSlash = '/';
			}
			$.each(directiveValues, function(index, value){
				// Sanitize values as folder directive, trailing and ending slashes
				directiveValues[index] = '/' + value.characterTrim('/', '\\') + endSlash;
			});
			
			// Everything is in place, now append the directive and show success message
			// Append text to the text area
			$('#htaccess_contents').val(function(_, val){
				return val + '\n' + directiveName + directiveValues.join(' '); 
			});
			
			// Scroll to bottom the textarea
			$("#htaccess_contents").scrollTop($("#htaccess_contents")[0].scrollHeight);
			
			// Reset value
			$('div.paths input').val('');
			
			// Show user message
			$('#system-message-container').html(messageSnippet);
			setTimeout(function(){
				$('.htaccess_messages').fadeOut(500, function(){
					$(this).remove();
				});
			},1000);
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
			// Extend String Object prototype
			String.prototype.characterTrim = function(character, escape) {
				var trimRegexp = new RegExp('^' + escape + character + '+', 'i');
				return this.replace(trimRegexp, "");
			}
			
			// Bind the main save event
			$('#htaccess_save').on('click', function(){
				// Store the current history version of the htaccess
				var currentHtaccess = $('#htaccess_contents').val();
				versioningHtaccess.push(currentHtaccess);
				
				// Now serialize in the sessionStorage
				sessionStorage.setItem('htaccess_history', JSON.stringify(versioningHtaccess));
			});
			
			// Manage dropdown
			directiveDropdown.on('change', function(jqEvent) {
				// Hide/Show fields and change label caption based on the
				// directive type
				var directive = $('option:selected', this).data('directive');
				// Single field input
				if (directive == '404') {
					$('*[data-role=extended]').hide();
					$('label[data-role=basic]').text(COM_JMAP_HTACCESS_PATH);
				} else {
					// Redirect with double fields
					$('*[data-role=extended]').show();
					$('label[data-role=basic]').text(COM_JMAP_HTACCESS_OLD_PATH);
				}
				
				// By default reset always validation
				$('div.paths input.error').removeClass('error');
				$('div.paths ul.errorlist').remove();
			});

			// Directive adder event
			$('#htaccess_adder').on('click', {
				bind : this
			}, function(jqEvent) {
				jqEvent.preventDefault();

				addHtaccessDirective.call(jqEvent.data.bind);
			});
			
			// Step before, restore the previous history version of the htaccess
			$('#htaccess_prev_versioning').on('click', function(jqEvent){
				if( window.sessionStorage !== null ) {
					// Discard the latest versioning, equal to the current file version
					if(versioningHtaccess.length) {
						versioningHtaccess.pop();
						// Reassign/Refresh the session storage history array of versioning
						sessionStorage.setItem('htaccess_history', JSON.stringify(versioningHtaccess));
					}
					
					var previousVersion = versioningHtaccess.pop();
					// If not null restore the original version
					if(previousVersion) {
						$('#htaccess_contents').val(previousVersion);
						$('input[name=restored]').val(1);
						$('#htaccess_save').trigger('click');
					} else {
						// We reached the bottom of the stack AKA the stack is empty, restore the original version
						$('#htaccess_restore').trigger('click');
					}
				}
			});
			
			// Restoration of the original session htaccess file
			$('#htaccess_restore').on('click', function(jqEvent){
				if( window.sessionStorage !== null ) {
					originalHtaccess = sessionStorage.getItem('htaccess');
					// If not null restore the original version
					if(originalHtaccess) {
						$('#htaccess_contents').val(originalHtaccess);
						$('input[name=restored]').val(1);
						$('#htaccess_save').trigger('click');
					}
					
					// Reset the history
					sessionStorage.removeItem('htaccess_history');
				}
			});
			
			// Bind validation reset
			$('div.paths input').on('keyup', function(jqEvent){
				$(this).removeClass('error');

				// Everything correct?
				if(!$('div.paths input').hasClass('error')) {
					$('div.paths ul.errorlist').remove();
				}
			});
			
			// Store the sessionStorage value of the original htaccess file before editing
			if( window.sessionStorage !== null ) {
				// Grab the current value of the htaccess textarea file, initialize it only if not available
				if(!sessionStorage.getItem('htaccess')) {
					originalHtaccess = $('#htaccess_contents').val();
					sessionStorage.setItem('htaccess', originalHtaccess);
				}
				
				// Initialize the versioning counter
				if(sessionStorage.getItem('htaccess_history')) {
					versioningHtaccess = JSON.parse(sessionStorage.getItem('htaccess_history'));
					// Is there some history versions?
					if(!versioningHtaccess.length) {
						$('#htaccess_prev_versioning').attr('disabled', 'disabled');
					}
					// Counter databind
					$('*[data-bind=versions_counter]').text(versioningHtaccess.length);
				} else {
					$('#htaccess_prev_versioning').attr('disabled', 'disabled');
				}
			}
			
			// Scroll to bottom the textarea
			$("#htaccess_contents").scrollTop($("#htaccess_contents")[0].scrollHeight);
			
			$('#fancy_closer').on('click', function(){
				parent.jQuery.fancybox.close();
				return false;
			});
			
			// Add desc popover
			$('label.hasrightPopover').popover({trigger:'hover', placement:'bottom', html:1});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapHtaccess = new Htaccess();
	});
})(jQuery);