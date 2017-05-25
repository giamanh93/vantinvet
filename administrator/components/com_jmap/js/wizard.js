/**
* JS APP Client for wizard data source creation
* 
* @package JMAP::WIZARD::administrator::components::com_jmap 
* @subpackage js 
* @author Joomla! Extensions Store
* @copyright (C)2015 Joomla! Extensions Store
* @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
jQuery(function($){
	var Wizard = $.newClass ({
		/**
		 * Generic selectors bunch
		 * @access public
		 * @property prototype
		 * @var String
		 */
		selectors : null,
	 
		/**
		 * Object initializer
		 * 
		 * @access public
		 * @param string selettore 
		 */
		init : function(injectedSelectors) {
			/**
			 * Init prototype properties (set method)
			 * @property prototype
			 */
			this.constructor.prototype.selectors = injectedSelectors;
			
			$('i.icon-list').parent().addClass('btn-success');
			
			// Start register events app
			this.registerEvents();
		},
	
		/**
		 * Register events for user interface interaction
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		registerEvents : function() {
			// Register user interaction
			$(this.selectors).on('click', {bind:this}, function(event) {
				// Prevent standard controller client
				event.preventDefault();
				
				// Only valida ancor <a>
				event.stopPropagation();
				
				// Start from prepare progress
				event.data.bind.openPrepareProgress(this, event.data.bind); 
			});
		},
		
		/**
		 * Open first prepare progress bar
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		openPrepareProgress : function(element, context) {
			//  Extension selected effective name
			var extensionName = $(element).data('extension');
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
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSINFOTITLE1 + '</h4>' +
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
				$('#progressInfo1').empty().append(COM_JMAP_PROGRESSINFOSUBTITLE1);
				setTimeout(function(){
					context.checkAjaxRequirements(extensionName)
					.then(function(response){
			        		isValid = !!response.extensionFound;
			        		if(isValid) {
			        			$('#progressInfo1').append(COM_JMAP_PROGRESSINFOSUBTITLE1_2);
								// Set 100% for progress
								$('#progressBar1').css({'width':'100%'});
								// Rethrow to create progress
								context.openCreateProgress(element, context);
			        		} else {
								$('#progressBar1').css({'width':'100%'}).addClass('progress-bar-danger');
								$('#progressInfo1').empty().append('<p>' + COM_JMAP_PROGRESSINFOSUBTITLE1_2ERROR + '</p>');
								setTimeout(function(){
									$('#progressModal1').modal('hide');
								}, 3000);
							}
		        	})
				}, 200);
			});
			
			$('#progressModal1').modal(modalOptions);
			
			// Remove backdrop after removing DOM modal
			$('#progressModal1').on('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
			});
		},
		
		/**
		 * Open first prepare progress bar
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		openCreateProgress : function(element, context) {
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
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSINFOTITLE2 + '</h4>' +
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
				$('#progressInfo2').append(COM_JMAP_PROGRESSINFOSUBTITLE2);
				setTimeout(function(){
					// Rethrow to make real ajax call
					context.startAjaxCreation(element);
				}, 800);
			});

			$('#progressModal2').modal(modalOptions);
		},
		
		/**
		 * Check with ajaxServer if extension chosen for data source creation 
		 * exists and is present in current Joomla installation, otherwise process
		 * will be stopped
		 * 
		 * @access public
		 * @method prototype
		 * @param String tableName
		 * @return Promise 
		 */
		checkAjaxRequirements : function(extensionName) { 
			// Is valid extension on this Joomla?
			var isValid = false;
			
			// Object to send to server
			var ajaxparams = { 
					idtask : 'checkExtension',
					template : 'json',
					param: extensionName
			     };
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams); 
			// Request JSON2JSON
			return $.ajax({
		        type:"POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        async: true,
		        data: {data : uniqueParam }
			}).promise();   
		},
		
		/**
		 * Make ajax POST restful submit to create a new data source for selected extension
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		startAjaxCreation : function(element) {
			$('#progressBar2').css({'width':'66%'});
			$.ajax({
		        type:"POST",
		        url: $(element).attr('href') + '&client=jsapp',
		        context: this,
		        success: function(response)  {
		        	// No response evaluation needed
		        	$('#progressInfo2').append(COM_JMAP_PROGRESSINFOSUBTITLE2_2);
					// Set 100% for progress
					$('#progressBar2').css({'width':'100%'});
					// Redirect user
					setTimeout(function(){
						// Call termination handler
						window.location.href = jmap_baseURI + 'index.php?option=com_jmap&task=sources.display';
						$('#progressModal1').modal('hide');
						$('#progressModal2').modal('hide');
					}, 800);
	            }
			});  
		}
	}); 
	
	// Start JS application
	$.wizard = new Wizard('a[data-role=start_create_process]');
});