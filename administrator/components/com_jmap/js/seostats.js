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
	var SeoStats = function() {
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @return Void
		 */
		var getSeoStatsData = function() {
			// Object to send to server
			var ajaxparams = {
				idtask : 'fetchSeoStats',
				template : 'json',
				param: {}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			// Request JSON2JSON
			var seoStatsPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(data === null) {
						// Error found
						defer.reject(COM_JMAP_NULL_RESPONSEDATA, 'notice');
						return false;
					}
					
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, data.errorlevel, data.seostats, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result && data.seostats) {
						defer.resolve(data.seostats);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1) + ': ' + jqXHR.status ;
					defer.reject(COM_JMAP_ERROR_HTTP + '-' + genericStatus + '- ' + errorThrown, 'error');
				});
			}).promise();

			seoStatsPromise.then(function(responseData) {
				// SEO stats correctly retrieved from the model layer, now updates the view accordingly
				formatSeoStats(responseData);
				
				// We have SEO stats, if sessionStorage is supported store data and avoid unuseful additional requests
				if( window.sessionStorage !== null ) {
					sessionStorage.setItem('seostats', JSON.stringify(responseData));
				}
			}, function(errorText, errorLevel, responseData, error) {
				// Do stuff and exit
				if(responseData) {
					formatSeoStats(responseData);
				}
				
				// Show little user error notification based on fatal php circumstances
				$('#seo_stats').append('<div class="alert alert-' + errorLevel + '">' + errorText + '</div>');
			}).always(function(){
				// Async request completed, now remove waiters info
				$('*.waiterinfo').remove();
			})
		};

		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @return Void
		 */
		var formatSeoStats = function(seoStats) {
			// Format stats
			$('li[data-bind=\\{google_pagerank\\}]').html('<span>' + seoStats.googlerank + '</span>');
			
			// Alexa rank
			var alexaRankFontSize = '';
			var alexaRankFormatted = seoStats.alexarank;
			if(!isNaN(parseInt(alexaRankFormatted))) {
				alexaRankFormatted = parseInt(alexaRankFormatted).toLocaleString().replace(/,/g, '.');
				if(alexaRankFormatted.length > 9) {
					alexaRankFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{alexa_rank\\}]').html('<span' + alexaRankFontSize + '>' + alexaRankFormatted + '</span>');
			
			// Alexa Site Backlinks
			var alexaBackLinksFontSize = '';
			var alexaBackLinksFormatted = seoStats.alexabacklinks;
			if(!isNaN(parseInt(alexaBackLinksFormatted))) {
				alexaBackLinksFormatted = parseInt(alexaBackLinksFormatted).toLocaleString().replace(/,/g, '.');
				if(alexaBackLinksFormatted.length > 9) {
					alexaBackLinksFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{alexa_backlinks\\}]').html('<span' + alexaBackLinksFontSize + '>' + alexaBackLinksFormatted + '</span>');
			
			// Alexa Page Load Time
			$('li[data-bind=\\{alexa_pageload_time\\}]').html('<span>' + seoStats.alexapageloadtime + '</span>');

			// Google SERP indexed links
			var indexedFontSize = '';
			if(seoStats.googleindexedlinks.length > 9) {
				indexedFontSize = ' style="font-size:18px"';
			}
			$('li[data-bind=\\{google_indexed_links\\}]').html('<span' + indexedFontSize + '>' + seoStats.googleindexedlinks + '</span>');

			// Alexa chart - Extract image link for the alexa chart
			var imageLink = $(seoStats.alexagraph).attr('src');
			$('li[data-bind=\\{alexa_graph\\}]').html('<a class="alexa_chart" href="' + imageLink + '">' + seoStats.alexagraph + '</a>');
			
			// SemRush Rank
			if(typeof(seoStats.semrushrank) !== 'undefined') {
				var semrushRankFontSize = '';
				var semrushRankFormatted = seoStats.semrushrank;
				if(!isNaN(parseInt(semrushRankFormatted))) {
					semrushRankFormatted = parseInt(semrushRankFormatted).toLocaleString().replace(/,/g, '.');
					if(semrushRankFormatted.length > 9) {
						semrushRankFontSize = ' style="font-size:18px"';
					}
				}
				$('li[data-bind=\\{semrush_rank\\}]').html('<span' + semrushRankFontSize + '>' + semrushRankFormatted + '</span>');
			}
			
			// SemRush Keywords
			if(typeof(seoStats.semrushkeywords) === 'object' && seoStats.semrushkeywords) {
				var semrushKeywords = '';
				$.each(seoStats.semrushkeywords.data, function(index, keywordObject){
					// Limit top 30 keywords
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(keywordObject.Ph) !== 'undefined') {
						semrushKeywords += ('<div>' + keywordObject.Ph + '</div>');
					}
				});
				$('li[data-bind=\\{semrush_keywords\\}]')
							.attr('data-content', semrushKeywords)
							.popover({trigger:'click', placement:'left', html:1})
							.addClass('clickable');
			}
			
			// SemRush competitors
			if(typeof(seoStats.semrushcompetitors) === 'object' && seoStats.semrushcompetitors) {
				var semrushCompetitors = '';
				$.each(seoStats.semrushcompetitors.data, function(index, domainObject){
					// Limit top 10 domains
					if((index + 1) > 10) {
						return false;
					}
					
					if(typeof(domainObject.Dn) !== 'undefined') {
						semrushCompetitors += ('<div>' + domainObject.Dn + '</div>');
					}
				});
				$('li[data-bind=\\{semrush_competitors\\}]')
							.attr('data-content', semrushCompetitors)
							.popover({trigger:'click', placement:'left', html:1})
							.addClass('clickable');
			}
			
			// SemRush Chart - Extract image link for the semrush chart
			if(typeof(seoStats.semrushgraph) !== 'undefined') {
				var semrushImageLink = $(seoStats.semrushgraph).attr('src');
				$('li[data-bind=\\{semrush_graph\\}]').html('<a class="semrush_chart" href="' + semrushImageLink + '">' + seoStats.semrushgraph + '</a>');
			}
			
			// Now bind fancybox effect on the newly appended alexa chart image
			$('li.fancybox-image a.alexa_chart')
				.attr('title', COM_JMAP_ALEXA_GRAPH)
				.fancybox({
					type: 'image',
			    	openEffect	: 'elastic',
			    	closeEffect	: 'elastic'
			});
			$('li.fancybox-image a.semrush_chart')
				.attr('title', COM_JMAP_SEMRUSH_GRAPH)
				.fancybox({
					type: 'image',
			    	openEffect	: 'elastic',
			    	closeEffect	: 'elastic'
			});
			
			// Show stats
			$('div.single_stat_rowseparator').fadeIn(200);
			$('#seo_stats div.single_stat_container').fadeIn(200);
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
			var containerWidth = $('#seo_stats').width(); 

			// Append waiter and text fetching data in progress
			$('#seo_stats').append('<div class="waiterinfo"></div>')
				.children('div.waiterinfo')
				.text(COM_JMAP_SEOSTATS_LOADING)
				.css({
					'position': 'absolute',
		            'top': '125px',
		            'left': (containerWidth - (parseInt(containerWidth / 2) + 75)) + 'px',
		            'width': '150px'
	        });
			
			$('#seo_stats').append('<img/>')
				.children('img')
				.attr({
					'src': jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif',
					'class': 'waiterinfo'})
				.css({
		            'position': 'absolute',
		            'top': '50px',
		            'left': (containerWidth - (parseInt(containerWidth / 2) + 32)) + 'px',
		            'width': '64px'
	        });
			
			// Check firstly if seostats are already in the sessionStorage
			if( window.sessionStorage !== null ) {
				var sessionSeoStats = sessionStorage.getItem('seostats');
				
				// Seo stats found in local session storage, go on to formatting data without a new request
				if(sessionSeoStats) {
					sessionSeoStats = JSON.parse(sessionSeoStats);
					
					// Format local data
					formatSeoStats(sessionSeoStats);
					
					// Remove waiter
					$('*.waiterinfo').remove();
					
					// Avoid to go on with a new async request
					return;
				}
			}
			
			// Get stats data from remote services using Promise, and populate user interface when resolved
			getSeoStatsData();

		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapSeoStats = new SeoStats();
	});
})(jQuery);