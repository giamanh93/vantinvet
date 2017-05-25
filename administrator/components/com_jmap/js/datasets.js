/**
 * Manage the JQuery UI Selectables to add data sources to datasets
 * Serialization in JSON format is carried on to pass data to server side to be
 * stored into database
 * 
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
'use strict';
(function($) {
	var Datasets = function() {
		/**
		 * Validates data sources selected
		 * 
		 *  @access public
		 *  @return boolean
		 */
		this.validateSelectable = function() {
			// Retrieve the number of data sources selected for this dataset
			var countSelectedSources = $('ol.datasources_selectable li.ui-selected').length
			
			// Validate if at least one data source is selected for the current dataset
			if(!countSelectedSources) {
				var requiredHtmlChunk = '<label class="validation sources label label-danger">' + COM_JMAP_SELECTONESOURCE + '</label>';
				// Check if validation label is not already in place
				if(!$('label.validation.sources').length) {
					$('#datasets_datasources td.left_title').append(requiredHtmlChunk);
				}
				return false;
			}
			
			return true;
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
			$('.datasources_selectable').selectable({
					stop : function(jqEvent, jqUIObject) {
						// Init and reset array of selected sources
						var selectedSources = new Array();
						
						// Add currently selected sources
						$( "ol.datasources_selectable li.ui-selected").each(function(k, elem) {
							// Add selected data sources ID to the selected sources array
							selectedSources.push($(elem).data('id'));
				        });
						
						// Now serialize and append to hidden POST field
						if(selectedSources.length) {
							var serializedSources = JSON.stringify(selectedSources);
							$('#sources').val(serializedSources);
						}
						
						// Remove any validation label
						$('label.validation.sources').remove();
					}
			});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapDatasets = new Datasets();
	});
})(jQuery);